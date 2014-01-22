<?php

namespace Trunkat\Tests;

use Trunkat\EnhancedUrlGenerator;
use Symfony\Component\HttpFoundation\ParameterBag;

class EnhancedUrlGeneratorTest extends \PHPUnit_Framework_TestCase
{
    private function getDependencies() {
        $routes  = $this->getMockBuilder('Symfony\Component\Routing\RouteCollection')
            ->disableOriginalConstructor()
            ->getMock();
        $context = $this->getMockBuilder('Symfony\Component\Routing\RequestContext')
            ->disableOriginalConstructor()
            ->getMock();
        $request = $this->getMockBuilder('Symfony\Component\HttpFoundation\Request')
            ->disableOriginalConstructor()
            ->getMock();

        return array($routes, $context, $request);
    }

    /**
     * @dataProvider testGetPreservedParamsProvider
     */
    public function testGetPreservedParams($paramsQuery, $paramsExpected, $paramsPreserve, $tokenKey) {
        list($routes, $context, $request) = $this->getDependencies();

        $request->query = new ParameterBag($paramsQuery);

        $urlGenerator = new EnhancedUrlGenerator($routes, $context, $request, array(
            'preserve'  => $paramsPreserve,
            'token'     => $tokenKey,
            'token_len' => false,
        ));

        $preserved = $urlGenerator->getPreservedParams();

        $this->assertEquals($preserved, $paramsExpected);
    }

    public function testGetPreservedParamsProvider()
    {
        return array(
            array(
              array(
                  'key1' => 'val1',
                  'key3' => 'val3',
                  'key4' => 'val4',
                  'tokenKey' => 'xxxYYYzzz',
              ),
              array(
                  'key1' => 'val1',
                  'key3' => 'val3',
                  'tokenKey' => 'xxxYYYzzz',
              ),
              array('key1', 'key2', 'key3'),
              'tokenKey',
            ),
            array(
              array(
                  'key1' => 'val1',
                  'key3' => 'val3',
                  'key4' => 'val4',
                  'tokenKey' => 'xxxYYYzzz',
              ),
              array(
                  'key1' => 'val1',
                  'key3' => 'val3',
              ),
              array('key1', 'key2', 'key3'),
              false,
            ),
        );
    }

    /**
     * @dataProvider testGenerateTokenProvider
     */
    public function testGenerateToken($tokenKey, $tokenLength)
    {
        list($routes, $context, $request) = $this->getDependencies();

        $request->query = new ParameterBag(array());

        $urlGenerator = new EnhancedUrlGenerator($routes, $context, $request, array(
            'preserve'  => array(),
            'token'     => $tokenKey,
            'token_len' => $tokenLength,
        ));

        $preserved = $urlGenerator->getPreservedParams();

        $this->assertEquals(strlen($preserved[$tokenKey]), $tokenLength);
    }

    public function testGenerateTokenProvider()
    {
        return array(
            array('key1', 56),
            array('k..2', 1),
            array('key_3', 100),
            array('key4', 1000),
        );
    }

    public function testSetDefaltParams()
    {
        list($routes, $context, $request) = $this->getDependencies();

        $request->query = new ParameterBag(array(
            'key1' => 'val1',
            'key3' => 'val3',
            'key4' => 'val4',
        ));

        $urlGenerator = new EnhancedUrlGenerator($routes, $context, $request, array(
            'preserve'  => array('key1', 'key2', 'key3', 'key5'),
            'token'     => false,
            'token_len' => false,
        ));

        $urlGenerator->setDefaultParams(array(
          'key5' => 'val5',
          'key3' => 'newvalue',
        ));

        $preserved = $urlGenerator->getPreservedParams();

        $this->assertEquals($preserved, array(
            'key1' => 'val1',
            'key3' => 'val3',
            'key5' => 'val5',
        ));
    }
}