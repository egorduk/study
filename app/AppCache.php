<?php

require_once __DIR__.'/AppKernel.php';

use Symfony\Bundle\FrameworkBundle\HttpCache\HttpCache;

class AppCache extends HttpCache
{
    /*protected function getOptions()
    {
        return array(
            'debug'                  => true,
            'default_ttl'            => 0,
            'private_headers'        => array('Authorization', 'Cookie'),
            'allow_reload'           => false,
            'allow_revalidate'       => false,
            'stale_while_revalidate' => 2,
            'stale_if_error'         => 60,
        );
    }


    protected function invalidate(Request $request)
    {
        if ('PURGE' !== $request->getMethod())
        {
            return parent::invalidate($request);
        }

        $response = new Response();

        if (!$this->getStore()->purge($request->getUri()))
        {
            $response->setStatusCode(404, 'Not purged');
        }
        else
        {
            $response->setStatusCode(200, 'Purged');
        }

        return $response;
    }*/
}
