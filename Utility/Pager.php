<?php

namespace NyroDev\UtilityBundle\Utility;

use NyroDev\UtilityBundle\Services\NyrodevService;

class Pager
{
    private int $nbPages = 1;

    public function __construct(
        private readonly NyrodevService $service,
        private string $route,
        private array $routePrm,
        private int $nbResults,
        private int $curPage = 1,
        private int $nbPerPage = 10,
    ) {
    }

    /**
     * Inidicates if the Pager has to paginate.
     */
    public function hasToPaginate(): bool
    {
        return $this->nbResults > $this->nbPerPage;
    }

    /**
     * Get the URL for a page number.
     *
     * @param int   $page     Page number
     * @param bool  $absolute Indicate if the URI should be absolute
     * @param array $routePrm Route parameter array to use instead of the configured one
     */
    public function getUrl(int $page, bool $absolute = false, ?array $routePrm = null): string
    {
        $prm = !is_null($routePrm) ? $routePrm : $this->routePrm;
        $prm['page'] = $page;

        return $this->service->generateUrl($this->route, $prm, $absolute);
    }

    /**
     * Get the first page number.
     */
    public function getFirst(): int
    {
        return 1;
    }

    /**
     * Get the current page URL.
     *
     * @param bool $absolute Indicate if the URI should be absolute
     */
    public function getCurrentUrl(bool $absolute = false): string
    {
        return $this->getUrl($this->getCurPage(), $absolute);
    }

    /**
     * Get the first page URL.
     *
     * @param bool $absolute Indicate if the URI should be absolute
     */
    public function getFirstUrl(bool $absolute = false): string
    {
        return $this->getUrl($this->getFirst(), $absolute);
    }

    /**
     * Indicates if there is a previous page.
     */
    public function hasPrevious(): bool
    {
        return $this->curPage > 1;
    }

    /**
     * Get the previous page number, null if not.
     */
    public function getPrevious(): ?int
    {
        return $this->hasPrevious() ? $this->curPage - 1 : null;
    }

    /**
     * Get the preivous page url, null if not.
     *
     * @param bool $absolute Indicate if the URI should be absolute
     */
    public function getPreviousUrl(bool $absolute = false): ?string
    {
        return $this->hasPrevious() ? $this->getUrl($this->getPrevious(), $absolute) : null;
    }

    /**
     * Indicates if there is a next page.
     */
    public function hasNext(): bool
    {
        return $this->curPage < $this->nbPages;
    }

    /**
     * Get the next page number, null if not.
     */
    public function getNext(): ?int
    {
        return $this->hasNext() ? $this->curPage + 1 : null;
    }

    /**
     * Get the next page url, null if not.
     *
     * @param bool $absolute Indicate if the URI should be absolute
     */
    public function getNextUrl(bool $absolute = false): ?string
    {
        return $this->hasNext() ? $this->getUrl($this->getNext(), $absolute) : null;
    }

    /**
     * Get the last page number.
     */
    public function getLast(): int
    {
        return $this->getNbPages();
    }

    /**
     * Get the last page URL.
     *
     * @param bool $absolute Indicate if the URI should be absolute
     */
    public function getLastUrl(bool $absolute = false): string
    {
        return $this->getUrl($this->getLast(), $absolute);
    }

    /**
     * Get Pages index URLs.
     *
     * @param int  $nb       Number of pages to be shown in the index
     * @param bool $absolute Indicate if the URI should be absolute
     */
    public function getPagesIndex(int $nb = 11, bool $absolute = false): array
    {
        $space = ($nb - 1) / 2;
        $start = $this->getCurPage() - $space;
        $end = $this->getCurPage() + $space;
        if ($start < 1 && $end > $this->getNbPages()) {
            $start = 1;
            $end = $this->getNbPages();
        } elseif ($start < 1) {
            $start = 1;
            $end = $nb - 1;
        } elseif ($end > $this->getNbPages()) {
            $end = $this->getNbPages();
            $start = $end - $nb + 1;
        }

        if ($start < 1) {
            $start = 1;
        }
        if ($end > $this->getNbPages()) {
            $end = $this->getNbPages();
        }

        $ret = [];
        for ($i = $start; $i <= $end; ++$i) {
            $ret[$i] = [
                $this->getUrl($i, $absolute),
                $i == $this->getCurPage(),
            ];
        }

        return $ret;
    }

    /**
     * Calculates the number of pages.
     */
    protected function calcNbPages(): void
    {
        if (!is_null($this->nbPerPage) && !is_null($this->nbResults)) {
            $this->nbPages = ceil($this->nbResults / $this->nbPerPage);
        }
    }

    /**
     * Get the start element to use when fetchings objects.
     */
    public function getStart(): int
    {
        return ($this->curPage - 1) * $this->nbPerPage;
    }

    /**
     * Get the number of pages.
     */
    public function getNbPages(): int
    {
        return $this->nbPages;
    }

    /**
     * Get the route parameters.
     */
    public function getRoutePrm(): array
    {
        return $this->routePrm;
    }

    /**
     * Set the route parameters.
     */
    public function setRoutePrm(array $routePrm): void
    {
        $this->routePrm = $routePrm;
    }

    /**
     * Get the service.
     */
    public function getService(): NyrodevService
    {
        return $this->service;
    }

    /**
     * Get the route name.
     */
    public function getRoute(): string
    {
        return $this->route;
    }

    /**
     * Set the route name.
     */
    public function setRoute(string $route): void
    {
        $this->route = $route;
    }

    /**
     * Get the current page.
     */
    public function getCurPage(): int
    {
        return $this->curPage;
    }

    /**
     * Set the current page.
     */
    public function setCurPage(int $curPage): void
    {
        $this->curPage = $curPage;
    }

    /**
     * Get the number of elements per page.
     */
    public function getNbPerPage(): int
    {
        return $this->nbPerPage;
    }

    /**
     * Set the number of elements per page.
     */
    public function setNbPerPage(int $nbPerPage): void
    {
        $this->nbPerPage = $nbPerPage;
        $this->calcNbPages();
    }

    /**
     * Get the number of results.
     */
    public function getNbResults(): int
    {
        return $this->nbResults;
    }

    /**
     * Set the number of results.
     */
    public function setNbResults(int $nbResults): void
    {
        $this->nbResults = $nbResults;
        $this->calcNbPages();
    }
}
