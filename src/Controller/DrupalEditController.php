<?php

namespace MakinaCorpus\Drupal\Layout\Controller;

use MakinaCorpus\Layout\Context\Context;
use MakinaCorpus\Layout\Context\EditToken;
use MakinaCorpus\Layout\Controller\EditController;
use MakinaCorpus\Layout\Storage\LayoutInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Drupal oriented layout actions controller
 */
class DrupalEditController extends EditController
{
    /**
     * {@inheritdoc}
     */
    protected function handleResponse(Request $request, array $ret)
    {
        if ($request->isXmlHttpRequest()) {
            return new JsonResponse($ret);
        }

        if (($url = $request->get('destination')) && !url_is_external($url)) {

            $parsed = drupal_parse_url($url);
            $path = $parsed['path'];

            $options = [];
            $options['query'] = $parsed['query'];
            $options['fragment'] = $parsed['fragment'];

            return new RedirectResponse(url($path, $options));
        }

        return new Response();
    }

    /**
     * Set page content here action
     */
    public function setPageAction(Request $request, Context $context, EditToken $token, LayoutInterface $layout, int $containerId, int $position = 0) : Response
    {
        return $this->addAction($request, $context, $token, $layout, $containerId, 'page', 1, $position);
    }
}
