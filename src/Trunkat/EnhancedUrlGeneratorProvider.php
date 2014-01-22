<?php

namespace Trunkat;

use Silex\Application;
use Silex\ServiceProviderInterface;
use Trunkat\EnhancedUrlGenerator;

/**
 * Routing component Provider for URL generation.
 */
class EnhancedUrlGeneratorProvider implements ServiceProviderInterface
{
    /**
    * {@inheritdoc}
    */
    public function register(Application $app)
    {
        $app['url_generator'] = $app->share(function ($app) {
            $app->flush();

            $configuration = array(
                'preserve'  => isset($app['url_generator.preserve']) ? $app['url_generator.preserve'] : array(),
                'token'     => isset($app['url_generator.token']) ? $app['url_generator.token'] : false,
                'token_len' => isset($app['url_generator.token_length']) ? $app['url_generator.token_length'] : false,
            );

            return new EnhancedUrlGenerator($app['routes'], $app['request_context'], $app['request'], $configuration);
        });
    }

    /**
    * {@inheritdoc}
    */
    public function boot(Application $app)
    {
    }
}