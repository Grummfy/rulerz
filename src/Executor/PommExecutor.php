<?php

namespace RulerZ\Executor;

use Hoa\Ruler\Model;
use PommProject\ModelManager\Model\Model as PommModel;

use RulerZ\Context\ExecutionContext;
use RulerZ\Visitor\PommVisitor;

/**
 * Execute a rule on Pomm.
 */
class PommExecutor implements ExtendableExecutor
{
    use Polyfill\ExtendableExecutor;
    use Polyfill\FilterBasedSatisfaction;

    /**
     * Constructs the Elasticsearch executor.
     *
     * @param array $operators A list of custom operators to register.
     */
    public function __construct(array $operators = [])
    {
        $this->registerOperators($operators);
    }

    /**
     * {@inheritDoc}
     */
    public function filter($target, Model $rule, array $parameters, ExecutionContext $context)
    {
        $whereClause = $this->buildWhereClause($rule, $parameters);
        $method      = !empty($context['method']) ? $context['method'] : 'findWhere';

        return call_user_func([$target, $method], $whereClause);
    }

    /**
     * {@inheritDoc}
     */
    public function supports($target, $mode)
    {
        // we make the assumption that pomm models use at least the
        // \PommProject\ModelManager\Model\ModelTrait\ReadQueries trait
        return $target instanceof PommModel;
    }

    /**
     * Builds the Where clause for the given rule.
     *
     * @param Model $rule       The rule to apply.
     * @param array $parameters The parameters used in the rule.
     *
     * @return Where The clause.
     */
    private function buildWhereClause(Model $rule, array $parameters)
    {
        $searchBuilder = new PommVisitor();
        $searchBuilder->setOperators($this->getOperators());
        $searchBuilder->setParameters($parameters);

        return $searchBuilder->visit($rule);
    }
}
