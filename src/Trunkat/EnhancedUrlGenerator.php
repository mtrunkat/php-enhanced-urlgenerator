<?php

namespace Trunkat;

use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ParameterBag;

/**
 * Standart Symfony url generator enhanced by functionality to
 * preserve given list of url parameters in every generated url.
 * It also allows to generate random token to indentify transaction.
 */
class EnhancedUrlGenerator extends UrlGenerator
{
    private $preserve;
    private $token;
    private $tokenLen;
    private $params = false;

    const DEFAULT_TOKEN_LENGTH = 32;
    const TOKEN_ALPHABET = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQSTUVWXYZ';

    /**
     * Constructor.
     *
     * @param \Symfony\Component\Routing\RouteCollection $routes
     * @param \Symfony\Component\Routing\RequestContext  $context
     * @param \Symfony\Component\HttpFoundation\Request  $request
     * @param array                                      $configuration
     */
    public function __construct(RouteCollection $routes, RequestContext $context, Request $request, $configuration)
    {
        parent::__construct($routes, $context);

        $this->preserve = $configuration['preserve'];
        $this->token    = $configuration['token'];
        $this->tokenLen = $configuration['token_len'] ?: self::DEFAULT_TOKEN_LENGTH;

        if ($this->token) {
            $this->preserve[] = $this->token;
        }

        $this->params = $this->getParamsFromQuery($request->query);

        if ($this->token && !$this->params->has($this->token)) {
            $this->params->set($this->token, $this->generateToken());
        }
    }

    /**
     * {@inheritdoc}
     */
    public function generate($name, $parameters = array(), $referenceType = self::ABSOLUTE_PATH)
    {
        $parameters = array_merge($this->getPreservedParams(), $parameters);

        return parent::generate($name, $parameters, $referenceType);
    }

    /**
     * Allows to set default values for preserved parameters.
     *
     * @param array $defaults
     */
    public function setDefaultParams($defaults)
    {
        foreach ($defaults as $key => $val) {
            if (!$this->params->has($key) && in_array($key, $this->preserve)) {
                $this->params->set($key, $val);
            }
        }
    }

    /**
     * Returns array of preserved parameters and their values.
     *
     * @return array
     */
    public function getPreservedParams()
    {
        return $this->params->all();
    }

    /**
     * Creates array of parameters with values from query.
     *
     * @param \Symfony\Component\HttpFoundation\ParameterBag $query
     *
     * @return \Symfony\Component\HttpFoundation\ParameterBag
     */
    private function getParamsFromQuery(ParameterBag $query)
    {
        $params = new ParameterBag;

        foreach ($this->preserve as $key) {
            if ($query->has($key)) {
                $val = htmlspecialchars($query->get($key));

                $params->set($key, $val);
            }
        }

        return $params;
    }

    /**
     * Generates random token.
     *
     * @return string
     */
    private function generateToken()
    {
        $chars  = self::TOKEN_ALPHABET;
        $string = '';

        for ($p = 0; $p < $this->tokenLen; $p ++) {
            $string .= $chars[mt_rand(0, strlen($chars) - 1)];
        }

        return $string;
    }
}