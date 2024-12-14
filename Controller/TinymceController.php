<?php

namespace NyroDev\UtilityBundle\Controller;

use NyroDev\UtilityBundle\Services\NyrodevService;
use NyroDev\UtilityBundle\Utility\TinymceBrowser;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class TinymceController extends AbstractController
{
    public function __construct(
        private readonly NyrodevService $nyrodevService,
        private readonly FormFactoryInterface $formFactory,
    ) {
    }

    public function browserAction(Request $request, string $type, string $dirName = 'tinymce'): Response
    {
        $tinymceBrowser = new TinymceBrowser(
            $this->nyrodevService,
            $this->formFactory,
            $request,
            $type,
            'uploads/'.$dirName
        );

        $response = $tinymceBrowser->getReponse();

        if ($response instanceof Response) {
            return $response;
        }

        return $this->render($response['view'], $response['prm']);
    }
}
