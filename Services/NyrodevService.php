<?php

namespace NyroDev\UtilityBundle\Services;

use DateTime;
use DateTimeZone;
use Doctrine\Persistence\ObjectRepository;
use Exception;
use Html2Text\Html2Text;
use NyroDev\UtilityBundle\Services\Traits\KernelInterfaceServiceableTrait;
use NyroDev\UtilityBundle\Utility\Pager;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class NyrodevService extends AbstractService
{
    use KernelInterfaceServiceableTrait;

    public function getKernel(): KernelInterface
    {
        return $this->getKernelInterface();
    }

    /**
     * Kernel request listener to setLocale if configured.
     */
    public function onKernelRequest(RequestEvent $event): void
    {
        if ($event->isMainRequest() && $this->getParameter('nyroDev_utility.setLocale')) {
            $locale = $event->getRequest()->getLocale();

            if (0 === strpos($locale, 'change_')) {
                $tmp = explode('change_', $locale);
                $locale = $tmp[1];
                // Update already instanciated objects
                $event->getRequest()->setLocale($locale);
                $this->get('translator')->setLocale($locale);
            }

            if (!defined('NYRO_LOCALE')) {
                define('NYRO_LOCALE', $locale);
            }

            $locales = [
                $locale,
                $locale.'@euro',
                $locale.'.utf8',
            ];
            if (2 == strlen($locale)) {
                $locUp = strtoupper($locale);
                $locale .= '_'.('ZH' == $locUp ? 'CN' : $locUp);
                $locales[] = $locale;
                $locales[] = $locale.'@euro';
                $locales[] = $locale.'.utf8';
            }
            foreach (array_reverse($locales) as $loc) {
                $tmp = setlocale(LC_ALL, $loc);
                if (mb_strtolower($tmp) == mb_strtolower($loc)) {
                    break;
                }
            }
        }
    }

    /**
     * Kernel response listener to add content-language if configured.
     */
    public function onKernelResponse(ResponseEvent $event): void
    {
        if ($this->getParameter('nyroDev_utility.setContentLanguageResponse') && $event->getResponse() && $event->getResponse()->headers && $event->getRequest()->getLocale()) {
            $event->getResponse()->headers->set('Content-Language', $event->getRequest()->getLocale());
        }
    }

    /**
     * Generates a URL from the given parameters, allowing extra parameters (like comma).
     */
    public function generateUrl(string $route, array $parameters = [], bool $absolute = false): string
    {
        if ('#' == $route) {
            return '#';
        }

        return str_replace('%2C', ',', $this->get('router')->generate($route, $parameters, $absolute ? UrlGeneratorInterface::ABSOLUTE_URL : UrlGeneratorInterface::ABSOLUTE_PATH));
    }

    /**
     * Absolutize an URL.
     */
    public function getFullUrl(string $path): string
    {
        if (0 === strpos($path, 'http') || 0 === strpos($path, 'mailto:') || 0 === strpos($path, '#')) {
            return $path;
        }
        $router = $this->get('router');
        $baseUrl = null;

        if ('/' != $path[0]) {
            $path = '/'.$path;
            $baseUrl = $router->getContext()->getBaseUrl();
        }

        if ($baseUrl && '/' != $baseUrl[0]) {
            $baseUrl = '/'.$baseUrl;
        }

        $host = $router->getContext()->getHost();
        $port = null;
        if ($router->getContext()->isSecure()) {
            $port = $router->getContext()->getHttpsPort();
            if ('443' == $port) {
                $port = null;
            }
        } else {
            $port = $router->getContext()->getHttpPort();
            if ('80' == $port) {
                $port = null;
            }
        }
        if ($port) {
            $host .= ':'.$port;
        }

        return $router->getContext()->getScheme().'://'.$host.$baseUrl.$path;
    }

    public function setSlot(string $name, mixed $value): void
    {
        $this->get('nyrodev.templating.helper.slots')->set($name, $value);
    }

    public function getAnalyticsUA(): ?string
    {
        return $this->getParameter('analyticsUA');
    }

    public function getTrackingAnalytics(): ?string
    {
        $ret = null;
        $ua = $this->getAnalyticsUA();
        if ($ua) {
            $ret = "<script>
  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');
  ga('create', '".$ua."', 'auto');
  ga('send', 'pageview');
</script>";
        }

        return $ret;
    }

    /**
     * Get url with assetic version if configured.
     */
    public function getAsseticVersionUrl(string $url): string
    {
        $tmp = $this->getParameter('assetic_versions');

        if ($tmp && is_array($tmp)) {
            $tmpUrl = basename($url);
            $url .= '?'.(isset($tmp[$tmpUrl]) && $tmp[$tmpUrl] ? $tmp[$tmpUrl] : (isset($tmp['global']) ? $tmp['global'] : ''));
        }

        return $url;
    }

    /**
     * Inline a texte to remove all new line and various characters.
     */
    public function inlineText(string $text): string
    {
        return preg_replace('/\s\s+/', ' ', preg_replace('/\s/', ' ', trim($text, " \t\n\r\0\x0B:·-")));
    }

    /**
     * Urlify a string.
     */
    public function urlify(string $text): string
    {
        $text = str_replace(
            ['ß', 'æ',  'Æ',  'Œ', 'œ', '¼',   '½',   '¾',   '‰',   '™', '&', '	', ' '], // Last characters are tabs and non-breaking space
            ['ss', 'ae', 'AE', 'OE', 'oe', '1/4', '1/2', '3/4', '0/00', 'TM', '_', ' ', ' '],
            $text);
        $from = "ÀÁÂÃÄÅàáâãäåÒÓÔÕÖØòóôõöøðÈÉÊËèéêëÇçÌÍÎÏìíîïÙÚÛÜùúûüŠšÝŸÿÑñÐÞþ()[]~¤$&%*@ç§¶!¡†‡?¿;,.#:/\\^¨€¢£¥{}|¦+÷×±<>«»“”„\"‘’' ˜–—…©®¹²³°";
        $to = 'AAAAAAaaaaaaOOOOOOoooooooEEEEeeeeCcIIIIiiiiUUUUuuuuSsYYyNnDPp           cS        ---     EcPY        __________------CR123-';

        return strtolower(trim(str_replace(
            [' ', '-----', '----', '---', '--'],
            '-',
            strtr(utf8_decode($text), utf8_decode($from), utf8_decode($to))), '-'));
    }

    /**
     * Get a random string.
     *
     * @param int    $len    String length
     * @param string $ignore Characters to exclude
     */
    public function randomStr(int $len = 20, ?string $ignore = null): string
    {
        $source = 'abcdefghikjlmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
        if (!is_null($ignore)) {
            $tmp = [];
            for ($i = 0; $i < strlen($ignore); ++$i) {
                $tmp[] = $ignore[$i];
            }
            $source = str_replace($tmp, '', $source);
        }
        $len = abs(intval($len));
        $n = strlen($source) - 1;
        $ret = '';
        for ($i = 0; $i < $len; ++$i) {
            $ret .= $source[rand(0, $n)];
        }

        return $ret;
    }

    public function isPost(): bool
    {
        return $this->getRequest()->isMethod('POST');
    }

    /**
     * Get a new random uniq key for a specific field on a given repository.
     *
     * @param ObjectRepository $repository The repository
     * @param string           $field      Field name
     * @param int              $length     Random string length
     */
    public function getNewUniqRandomKey(ObjectRepository $repository, string $field, int $length): string
    {
        $entity = true;
        while ($entity) {
            $random = $this->randomStr($length);
            $entity = $repository->findOneBy([$field => $random]);
        }

        return $random;
    }

    /**
     * Returns true if the request is Ajax.
     * It works if your JavaScript library set an X-Requested-With HTTP header.
     * It is known to work with Prototype, Mootools, jQuery.
     */
    public function isAjax(): bool
    {
        return $this->getRequest()->isXmlHttpRequest();
    }

    protected ?bool $isExternalAgent = null;

    /**
     * Return true if the request comes from an external agent (like facebook external hit).
     */
    public function isExternalAgent(): bool
    {
        if (is_null($this->isExternalAgent)) {
            $this->isExternalAgent = false;
            if (isset($_SERVER['HTTP_USER_AGENT'])) {
                $ua = strtolower($_SERVER['HTTP_USER_AGENT']);
                if (false !== strpos($ua, 'facebookexternalhit') || preg_match('~(bot|crawl|external|snippet)~i', $ua)) {
                    $this->isExternalAgent = true;
                }
            }
        }

        return $this->isExternalAgent;
    }

    /**
     * Indicates if the PHP limits have been increased.
     */
    protected bool $increasedPhpLimits = false;

    /**
     * Increase PHP Limits.
     */
    public function increasePhpLimits()
    {
        if (!$this->increasedPhpLimits) {
            $this->increasedPhpLimits = true;
            @set_time_limit(0);
            @ini_set('memory_limit', '-1');
        }
    }

    /**
     * Create a pager.
     */
    public function getPager(string $route, array $routePrm, int $total, int $page, int $nbPerPage): Pager
    {
        return new Pager($this, $route, $routePrm, $total, $page, $nbPerPage);
    }

    /**
     * Get the file extension.
     */
    public function getExt(string $file): ?string
    {
        $tmp = explode('?', pathinfo($file, PATHINFO_EXTENSION));

        return $tmp[0];
    }

    protected array $uniqFileNames = [];

    /**
     * Get a new uniq filename in a directory.
     *
     * @param string $dir  Destination directory
     * @param string $name Original filename
     * @param string $sep  Spearator used in case file already exists
     */
    public function getUniqFileName(string $dir, string $name, string $sep = '-'): string
    {
        list($name, $ext) = $this->standardizeFileName($name, true);

        $nameF = $name.'.'.$ext;
        $i = 2;
        while (isset($this->uniqFileNames[$dir.'/'.$nameF]) || file_exists($dir.'/'.$nameF)) {
            $nameF = $name.$sep.$i.'.'.$ext;
            ++$i;
        }
        $this->uniqFileNames[$dir.'/'.$nameF] = true;

        return $nameF;
    }

    /**
     * Standardize filename using urlify function and keeping the extension.
     *
     * @param string $name    Original filename
     * @param bool   $asArray Indicates if the return should be an array or a string
     */
    public function standardizeFileName(string $name, bool $asArray = false): string|array
    {
        $name = mb_strtolower($name);
        $ext = $this->getExt($name);
        if ($ext) {
            $pos = strpos($name, $ext);
            if ($pos > 0) {
                $name = substr($name, 0, $pos);
            }
        }
        $name = $this->urlify($name);

        return $asArray ? [
            $name,
            $ext,
        ] : $name.($ext ? '.'.$ext : '');
    }

    /**
     * Get a human file size.
     *
     * @param string $file The file path
     *
     * @return string The human size
     */
    public function humanFileSize(string $file): string
    {
        $size = filesize($file);
        $mod = 1024;
        $units = explode(' ', 'B KB MB GB TB PB');
        for ($i = 0; $size > $mod; ++$i) {
            $size /= $mod;
        }

        return round($size, 2).' '.$units[$i];
    }

    protected bool $html2textLoaded = false;

    /**
     * Transform HTML content into text.
     */
    public function html2text(string $html): string
    {
        if (!$this->html2textLoaded) {
            require dirname(__FILE__).'/../Utility/Html2Text.php';
            $this->html2textLoaded = true;
        }
        $html2text = new Html2Text($html);

        return $html2text->get_text();
    }

    /**
     * Join rows in a single string.
     */
    public function joinRows(array $rows, string $separator = ', '): string
    {
        $ret = [];
        foreach ($rows as $r) {
            $ret[] = $r.'';
        }

        return implode($separator, $ret);
    }

    /**
     * Check if the current URL is matching the desired URL and return a redirect response if not.
     *
     * @param string $url         The desired URL
     * @param array  $allowParams List of allowed parameters
     */
    public function redirectIfNotUrl(string $url, array $allowParams = []): RedirectResponse|bool
    {
        if ($url != $this->getRequest()->getRequestUri()) {
            $redirect = true;
            $newArgs = [];
            try {
                $tmp = parse_url($this->getRequest()->getRequestUri());
                if (isset($tmp['path']) && $tmp['path'] == $url) {
                    $redirect = false;
                }
                if (isset($tmp['query'])) {
                    parse_str($tmp['query'], $args);
                    $alloweds = array_merge($allowParams, $this->getParameter('nyroDev_utility.redirectIfNotUrl_params', []));
                    foreach ($alloweds as $k) {
                        if (isset($args[$k])) {
                            $newArgs[$k] = $args[$k];
                        }
                    }
                    $redirect = count($newArgs) != count($args);
                }
            } catch (Exception $e) {
                $redirect = false;
            }
            if ($redirect) {
                return new RedirectResponse($url.(count($newArgs) ? '?'.http_build_query($newArgs) : ''), 301);
            }
        }

        return false;
    }

    /**
     * Format a date using strftime.
     *
     * @param string $format    Format translation ident
     * @param bool   $useOffset Use offset of datetime
     */
    public function formatDate(DateTime $datetime, string $format, ?bool $useOffset = null): string
    {
        if (is_null($useOffset)) {
            $useOffset = $this->getParameter('nyroDev_utility.dateFormatUseOffsetDefault');
        }

        $offset = 0;
        if ($useOffset) {
            $tz = new DateTimeZone(date_default_timezone_get());
            $offset = -1 * $tz->getOffset($datetime) + $datetime->getOffset();
        }

        return strftime($this->trans($format), $datetime->getTimestamp() + $offset);
    }

    /**
     * Truncate text to not be too large.
     *
     * @param string $text   Text to truncate
     * @param int    $limit  Limit
     * @param bool   $isFile Indicate if it should be treated as file to keep the extension
     *
     * @parame string $encoding Encoding to use
     *
     * @return string Trucnated text
     */
    public function truncate(string $text, int $limit, bool $isFile = false, string $encoding = 'UTF-8'): string
    {
        $ext = null;
        if ($isFile) {
            $ext = $this->getExt($text);
            if ($ext) {
                $limit -= mb_strlen($ext, $encoding);
                $text = mb_substr($text, 0, -mb_strlen($ext, $encoding) - 1);
            }
        }

        if (mb_strlen($text, $encoding) > $limit) {
            $text = mb_substr($text, 0, $limit - 3, $encoding).'...';
        }

        return $text.($ext ? '.'.$ext : null);
    }

    protected function getCryptKey(): string
    {
        return sha1($this->getParameter('secret'));
    }

    protected function doCryptAction(string $action, string $string, bool $excludeSlash = true): string
    {
        $cryptKey = $this->getCryptKey();

        $secret_key = substr($cryptKey, 0, 20);
        $secret_iv = substr($cryptKey, 21);
        $encrypt_method = 'AES-256-CBC';

        // hash
        $key = hash('sha256', $secret_key);

        // iv - encrypt method AES-256-CBC expects 16 bytes - else you will get a warning
        $iv = substr(hash('sha256', $secret_iv), 0, 16);

        if ('encrypt' === $action) {
            $output = base64_encode(openssl_encrypt($string, $encrypt_method, $key, 0, $iv));

            if ($excludeSlash && false !== strpos($output, '/')) {
                $output = $this->doCryptAction('encrypt', $string, $excludeSlash);
            }
        } elseif ('decrypt' === $action) {
            $output = openssl_decrypt(base64_decode($string), $encrypt_method, $key, 0, $iv);
        }

        return $output;
    }

    public function crypt(string $text, bool $excludeSlash = true): string
    {
        return $this->doCryptAction('encrypt', $text, $excludeSlash);
    }

    public function decrypt(string $encoded): string
    {
        return $this->doCryptAction('decrypt', $encoded);
    }
}
