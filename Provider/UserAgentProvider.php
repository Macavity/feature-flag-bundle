<?php

declare(strict_types=1);

namespace Shopping\FeatureFlagBundle\Provider;

use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Class UserAgentProvider.
 */
class UserAgentProvider extends AbstractValueProvider
{
    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @param RequestStack $requestStack
     * @param array        $values
     */
    public function __construct(RequestStack $requestStack, array $values)
    {
        $this->requestStack = $requestStack;

        parent::__construct($values);
    }

    /**
     * Returns the value given by the user.
     *
     * @param string $featureFlag
     * @return string
     */
    protected function getValue(string $featureFlag): string
    {
        return $this->requestStack->getCurrentRequest()->headers->get('User-Agent');
    }
}
