<?php

namespace NyroDev\UtilityBundle\Utility;

use Symfony\Component\HttpFoundation\Response;

/**
 * Class TransparentPixelResponse.
 *
 * @author Luciano Mammino <lucianomammino@gmail.com>
 * from http://loige.com/transparent-pixel-response-with-symfony-how-to-track-email-opening/
 */
class TransparentPixelResponse extends Response
{
    /**
     * Base 64 encoded contents for 1px transparent gif and png.
     *
     * @var string
     */
    public const IMAGE_CONTENT = 'R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw==';

    /**
     * The response content type.
     *
     * @var string
     */
    public const CONTENT_TYPE = 'image/gif';

    /**
     * Constructor.
     */
    public function __construct()
    {
        $content = base64_decode(self::IMAGE_CONTENT);
        parent::__construct($content);
        $this->headers->set('Content-Type', self::CONTENT_TYPE);
        $this->setPrivate();
        $this->headers->addCacheControlDirective('no-cache', true);
        $this->headers->addCacheControlDirective('must-revalidate', true);
    }
}
