<?php

namespace App\Service;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;


class FlagService
{

    /**
     * @var ParameterBagInterface
     */
    private $parameters;


    /**
     * FlagService constructor.
     *
     * @param ParameterBagInterface $parameters
     */
    public function __construct(ParameterBagInterface $parameters)
    {
        $this->parameters = $parameters;
    }


    public function getFlags()
    {
        $flags = [];

        foreach ($this->parameters->get('bird.flags') as $id => $flag) {
            $flags[$id] = array_merge(['id' => $id], $flag);
        }

        return $flags;
    }


    /**
     * @return array
     */
    public function getCommunityFlagPatterns()
    {
        $patterns = $this->parameters->get('bird.flag_patterns');

        $results = [];

        if (isset($patterns['communities']) && is_array($patterns['communities'])) {
            foreach ($patterns['communities'] as $key => $pattern) {
                $results[] = ['pattern' => $pattern, 'flag' => $this->getFlags()[$key]];
            }
        }

        return $results;
    }


    /**
     * @return array
     */
    public function getLargeCommunityFlagPatterns()
    {
        $patterns = $this->parameters->get('bird.flag_patterns');

        $results = [];

        if (isset($patterns['large_communities']) && is_array($patterns['large_communities'])) {
            foreach ($patterns['large_communities'] as $key => $pattern) {
                $results[] = ['pattern' => $pattern, 'flag' => $this->getFlags()[$key]];
            }
        }

        return $results;
    }


    /**
     * @return array
     */
    public function getExtendedCommunityFlagPatterns()
    {
        $patterns = $this->parameters->get('bird.flag_patterns');

        $results = [];

        if (isset($patterns['extended_communities']) && is_array($patterns['extended_communities'])) {
            foreach ($patterns['extended_communities'] as $key => $pattern) {
                $results[] = ['pattern' => $pattern, 'flag' => $this->getFlags()[$key]];
            }
        }

        return $results;
    }

}
