<?php

declare(strict_types=1);

/*
 * This file is part of the Gitlab API library.
 *
 * (c) Matt Humphrey <matth@windsor-telecom.co.uk>
 * (c) Graham Campbell <hello@gjcampbell.co.uk>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Gitlab\Api;

use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\Exception\UndefinedOptionsException;
use Symfony\Component\OptionsResolver\Options;

class Topics extends AbstractApi
{

    /**
     * @param array $parameters {
     *     @var string  $search           return list of topics matching the search criteria
     *     @var boolean $without_projects limit results to topics without assigned projects
     * }
     *
     * @throws UndefinedOptionsException If an option name is undefined
     * @throws InvalidOptionsException   If an option doesn't fulfill the specified validation rules
     *
     * @return mixed
     */
    public function all(array $parameters = [])
    {
        $resolver = $this->createOptionsResolver();
        $booleanNormalizer = function (Options $resolver, $value): string {
            return $value ? 'true' : 'false';
        };
        $resolver->setDefined('without_projects')
            ->setAllowedTypes('without_projects', 'bool')
            ->setNormalizer('without_projects', $booleanNormalizer)
        ;
        $resolver->setDefined('search');

        return $this->get('topics', $resolver->resolve($parameters));
    }
}
