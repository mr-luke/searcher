<?php

namespace Mrluke\Searcher;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use InvalidArgumentException;

/**
 * Searcher is a package for Laravel's Eloquent that perform
 * quering, filtering & sorting based on REST Url query.
 *
 * @author    Łukasz Sitnicki (mr-luke)
 *
 * @link      http://github.com/mr-luke/searcher
 *
 * @category  Laravel
 *
 * @license   MIT
 *
 * @version   1.0.0
 */
class Searcher
{
    /**
     * Model of filterable resource.
     *
     * @var \Illuminate\Database\Eloquent\Builder
     */
    private $b;

    /**
     * Determine if all query has been already processed.
     *
     * @var bool
     */
    private $built = false;

    /**
     * List of querable attributes.
     *
     * @var array
     */
    private $fields;

    /**
     * Array of request inputs.
     *
     * @var array
     */
    private $inputs;

    /**
     * Integrator connects.
     *
     * @var string
     */
    const INT = ' ';

    /**
     * Allowed Operators with mapping.
     * Resource: OData logical operators.
     *
     * @var array
     */
    const OPS = ['gt' => '>', 'ge' => '>=', 'lt' => '<', 'le' => '<=', 'ne' => '<>'];

    /**
     * Array of options.
     *
     * @var array
     */
    private $options;

    /**
     * Separator for multiple field inputs.
     *
     * @var string
     */
    const SEP = ',';

    /**
     * Sorting operators with mapping.
     *
     * @var array
     */
    const SOPS = ['+' => 'asc', '-' => 'desc'];

    /**
     * Array of query build for given inputs.
     *
     * @var array
     */
    private $query = [];

    public function __construct(array $options)
    {
        $this->options = $options;
    }

    /**
     * Return records fits to query (with possible REST pagination).
     *
     * @return \Illuminate\Support\Collection
     */
    public function get() : Collection
    {
        if ($this->getOption('auto_pagination') && $this->detectPagination()) {
            // Set up offset and limit due to request params.
            // Dedicated useage for API and REST styled pagination.
            return $this->process()->offset($this->getOffset())->limit($this->getLimit())->get();
        } else {
            return $this->process()->get();
        }
    }

    /**
     * Return builder with all scopes & queries applied.
     *
     * @return Illuminate\Databas\Eloquent\Builder
     */
    public function getBuilder() : Builder
    {
        if (!$this->built) {
            $this->process();
        }

        return $this->b;
    }

    /**
     * Return query map for given inputs.
     *
     * @return array
     */
    public function getQueryMap() : array
    {
        if (!$this->built) {
            $this->process();
        }

        return $this->query;
    }

    /**
     * Return paginated listing of records.
     *
     * @param int|null $limit
     * @param int|null $offset
     *
     * @return mixed
     */
    public function paginate(int $limit = null, int $offset = null)
    {
        if (is_null($limit)) {
            $limit = $this->getLimit($thi->getOption('limit'));
        }
        if (is_null($offset)) {
            $offset = $this->getOffset();
        }

        if ($this->getOption('api_mode')) {
            return $this->process()->offset($offset)->limit($limit)->get();
        }

        return $this->process()->paginate($limit, ['*'], 'page', $offset);
    }

    /**
     * Set model, it's configuration and optionaly builder.
     *
     * @param string|array                              $model
     * @param null|Illuminate\Database\Eloquent\Builder $builder
     *
     * @return self
     */
    public function setModel($model, Builder $builder = null) : self
    {
        // We get Builder for given $model and check if the model is properly
        // configurated for Searcher. By default as inputs is set
        // an array of \Illuminate\Http\Request::class.

        if (is_array($model)) {
            if (!$builder instanceof Builder) {
                throw new InvalidArgumentException(
                    'If config array given directly, $builder must be an instance of Illuminate\Database\Eloquent\Builder.'
                );
            }

            $this->fields = $model;
            $this->b = $builder;
        } else {
            if (!method_exists($model, 'getSearchableConfig')) {
                throw new InvalidArgumentException(
                    sprintf('Given %s is not an instance of Mrluke\Searcher\Contracts\Searchable.', $model)
                );
            }

            $this->fields = $model::getSearchableConfig();
            $this->b = $builder ?? $model::query();
        }

        $this->inputs = request()->all();

        return $this;
    }

    /**
     * Set options array as merged with global one.
     *
     * @param array $options
     *
     * @return self
     */
    public function setOptions(array $options) : self
    {
        $this->options = array_merge($this->options, $options);

        return $this;
    }

    /**
     * Set inputs array for searching.
     *
     * @param array $inputs
     *
     * @return self
     */
    public function setQuery(array $inputs) : self
    {
        $this->inputs = $inputs;

        return $this;
    }

    /**
     * Builds Builder query based on decoded inputs.
     *
     * @return void
     */
    private function buildQuery() : void
    {
        foreach ($this->query as $g) {
            $insert = $g['insert'];

            // Consider what is a group type and perform proper Eloquent
            // method for each of nested wheres.
            if ($g['type'] == 'has') {
                $op = in_array($insert[0][2], self::OPS) ? $insert[0][2] : null;
                $val = is_null($op) ? $insert[0][2] : $insert[0][3];

                $this->b->has($g['relation'], $op, (int) $val, $insert[0][4]);
            } elseif ($g['type'] == 'whereHas') {
                $this->b->whereHas($g['relation'], function ($query) use ($insert) {
                    foreach ($insert as $i) {
                        $method = array_shift($i);
                        $query->$method(...array_values($i));
                    }
                });
            } elseif ($g['type'] == 'whereNested') {
                $this->b->whereNested(function ($query) use ($insert) {
                    foreach ($insert as $i) {
                        $method = array_shift($i);
                        $query->$method(...array_values($i));
                    }
                });
            } elseif ($g['type'] == 'orderBy') {
                $this->b->orderBy($insert[0], $insert[1]);
            }
        }
    }

    /**
     * Compose where insert.
     *
     * @param array  $kind
     * @param mixed  $op
     * @param mixed  $val
     * @param string $bool
     *
     * @return array
     */
    private function composeWhere(array $kind, $op, $val, string $bool) : array
    {
        $method = $kind['method'];
        $field = $kind['field'];

        if ($method == 'whereLike') {
            $method = 'where';

            $op = (!is_null($op) && $op == '<>') ? 'NOT LIKE' : 'LIKE';
            $val = '%'.$val.'%';
        } elseif (in_array($method, ['whereIn', 'whereNull'])) {
            $val = (!is_null($op) && $op == '<>') ? true : false;
            $op = $bool;
            unset($bool);
        }

        if (is_null($op)) {
            $op = '=';
        }

        return compact('method', 'field', 'op', 'val', 'bool');
    }

    /**
     * Decodes filtering from given inputs.
     *
     * @return void
     */
    private function decodeFilter() : void
    {
        // For each of configurated filter fileds we process
        // request to check if there's one. In case of found
        // we process te sytanx.
        foreach ($this->fields['filter'] as $k => $f) {
            if (!array_key_exists($k, $this->inputs)) {
                continue;
            }
            // Now query like name=john,or+nick,ne+steve& is decoded.
            $input = $this->inputs[$k];
            // We need instruction of query's kind
            $kind = $this->getNestingSet($f);
            $wheres = [];

            // If nesting type is 'has' the given inputs must
            // have only one definition for field.
            if ($kind['type'] == 'has' && count(explode(self::SEP, $input)) > 1) {
                continue;
            }

            foreach (explode(self::SEP, $input) as $con) {
                $c = explode(self::INT, $con);
                $bool = 'and';

                // Check if there is logical connection operator
                // and if comperison operator is valid.
                if (count($c) > 1 && ($c[0] == 'or' or $c[0] == 'and')) {
                    $bool = array_shift($c);
                }
                if (count($c) > 1 && array_key_exists($c[0], self::OPS)) {
                    $op = self::OPS[array_shift($c)];
                }

                $wheres[] = $this->composeWhere($kind, $op ?? null, (is_array($c) ? implode(' ', $c) : $c), $bool);
            }

            unset($kind['method']);
            unset($kind['field']);

            if (!empty($wheres)) {
                $this->query[] = array_merge($kind, ['insert' => $wheres]);
            }
        }
    }

    /**
     * Decodes quering from given inputs.
     *
     * @return void
     */
    private function decodeQuery() : void
    {
        // There's no query requirement -> skip
        if (!empty($this->inputs['q'])) {
            // We build chain of OR WHERE LIKE for each of configurated
            // fileds in model one by one.
            $inputs = explode(self::SEP, $this->inputs['q']);
            $wheres = [];

            foreach ($this->fields['query'] as $f) {
                foreach ($inputs as $i) {
                    $wheres[] = ['where', $f, 'LIKE', '%'.$i.'%', 'or'];
                }
            }

            if (!empty($wheres)) {
                $this->query[] = ['type' => 'whereNested', 'insert' => $wheres];
            }
        }
    }

    /**
     * Decodes sorting from given inputs.
     *
     * @return void
     */
    private function decodeSort() : void
    {
        // There's no sort requirement -> skip
        if (!empty($this->inputs['sort'])) {
            // REST sorting is presented by fileds name
            // connected by comma.
            // Order type is determined by prefix of field name
            // 1) + for ASC
            // 2) - for DESC
            $sorts = explode(self::SEP, $this->inputs['sort']);

            foreach ($sorts as $s) {
                $op = substr($s, 0, 1);
                $f = substr($s, 1);
                // Check if model allows to sort by & operator is legit.
                if (!array_key_exists($op, self::SOPS)) {
                    continue;
                }
                if (!array_key_exists($f, $this->fields['sort'])) {
                    continue;
                }

                $this->query[] = ['type' => 'orderBy', 'insert' => [$this->fields['sort'][$f], self::SOPS[$op]]];
            }
        }
    }

    /**
     * Detects if REST request is paginated.
     *
     * @return void
     */
    private function detectIfPaginated() : boolean
    {
        return isset($this->inputs['limit']) && $this->inputs['limit'] > 0;
    }

    /**
     * Return offset of pages.
     *
     * @param int|null $default
     *
     * @return int
     */
    private function getLimit($default = null) : int
    {
        return $this->inputs['limit'] ?? $default;
    }

    /**
     * Return nesting set for query.
     *
     * @param string $field
     *
     * @return array
     */
    private function getNestingSet(string $field) : array
    {
        $data = [];
        $config = explode('.', $field);

        if ($config[0] == 'has' && count($config) == 3) {
            $data = [
                'type'     => 'whereHas',
                'relation' => $c[1],
                'method'   => 'where',
                'field'    => $c[2],
            ];
        } elseif ($config[0] == 'has' && count($config) == 2) {
            $data = [
                'type'     => 'has',
                'relation' => $c[1],
                'method'   => null,
                'field'    => null,

            ];
        } elseif (in_array($config[0], ['in', 'like', 'null']) && count($config) == 2) {
            $data = [
                'type'   => 'whereNested',
                'method' => 'where'.ucfirst($config[0]),
                'field'  => $c[1],
            ];
        } elseif (count($config) == 1) {
            $data = [
                'type'   => 'whereNested',
                'method' => 'where',
                'field'  => $field,
            ];
        }

        return $data;
    }

    /**
     * Return offset of pages.
     *
     * @param int $deafult
     *
     * @return int
     */
    private function getOffset($default = 0) : int
    {
        return $this->inputs['offset'] ?? $default;
    }

    /**
     * Return options.
     *
     * @var mixed
     */
    private function getOption(string $key)
    {
        return $this->options[$key] ?? $default;
    }

    /**
     * Process all search actions.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    private function process()
    {
        // Check if is already processed is case of avoid
        // query duplication.
        if ($this->built) {
            return $this->b;
        }

        // We apply to Builder query and others filters
        // in presented order for allowed & configurated.
        if ($this->getOption('allow_query') && isset($this->fields['query'])) {
            $this->decodeQuery();
        }

        if ($this->getOption('allow_filter') && isset($this->fields['filter'])) {
            $this->decodeFilter();
        }

        if ($this->getOption('allow_sort') && isset($this->fields['sort'])) {
            $this->decodeSort();
        }

        $this->buildQuery();

        $this->built = true;

        return $this->b;
    }
}
