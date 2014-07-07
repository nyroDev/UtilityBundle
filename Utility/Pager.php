<?php
namespace NyroDev\UtilityBundle\Utility;

use NyroDev\UtilityBundle\Services\MainService;

/**
 * Pager utility object 
 */
class Pager {

	/**
	 * nyroDev service object used to generate the URL
	 *
	 * @var MainService
	 */
	protected $service;
	
	/**
	 * Route string name.
	 *
	 * @var string
	 */
	protected $route;
	
	/**
	 * Route parameter used to generate the URL.
	 * Page parameter will be added
	 * 
	 * @var array
	 */
	protected $routePrm;
	
	/**
	 * Number of results
	 *
	 * @var int
	 */
	protected $nbResults;
	
	/**
	 * Current page number
	 * 
	 * @var int
	 */
	protected $curPage;
	
	/**
	 * Number of elements by page
	 *
	 * @var int
	 */
	protected $nbPerPage;
	
	/**
	 * Number of pages
	 *
	 * @var int
	 */
	protected $nbPages;

	/**
	 * Constructor of Pager utility
	 *
	 * @param MainService $service
	 * @param type $route
	 * @param array $routePrm
	 * @param int $nbResults
	 * @param int $curPage
	 * @param int $nbPerPage 
	 */
	public function __construct(MainService $service, $route, array $routePrm, $nbResults, $curPage = 1, $nbPerPage = 10) {
		$this->setService($service);
		$this->setRoute($route);
		$this->setRoutePrm($routePrm);
		$this->setNbResults($nbResults);
		$this->setCurPage($curPage);
		$this->setNbPerPage($nbPerPage);
	}

	/**
	 * Inidicates if the Pager has to paginate
	 *
	 * @return boolean
	 */
	public function hasToPaginate() {
		return $this->nbResults > $this->nbPerPage;
	}
	
	/**
	 * Get the URL for a page number
	 *
	 * @param int $page Page number
	 * @param boolean $absolute Indicate if the URI should be absolute
	 * @param array $routePrm Route parameter array to use instead of the configured one
	 * @return string The URL
	 */
	public function getUrl($page, $absolute = false, $routePrm = null) {
		$prm = !is_null($routePrm) ? $routePrm : $this->routePrm;
		$prm['page'] = $page;
		return $this->service->generateUrl($this->route, $prm, $absolute);
	}
	
	/**
	 * Get the first page number
	 *
	 * @return int 
	 */
	public function getFirst() {
		return 1;
	}
	
	/**
	 * Get the current page URL
	 *
	 * @param boolean $absolute Indicate if the URI should be absolute
	 * @return string
	 */
	public function getCurrentUrl($absolute = false) {
		return $this->getUrl($this->getCurPage(), $absolute);
	}
	
	/**
	 * Get the first page URL
	 *
	 * @param boolean $absolute Indicate if the URI should be absolute
	 * @return string
	 */
	public function getFirstUrl($absolute = false) {
		return $this->getUrl($this->getFirst(), $absolute);
	}
	
	/**
	 * Indicates if there is a previous page
	 * 
	 * @return boolean
	 */
	public function hasPrevious() {
		return $this->curPage > 1;
	}
	
	/**
	 * Get the previous page number, null if not
	 * 
	 * @return int|null
	 */
	public function getPrevious() {
		return $this->hasPrevious() ? $this->curPage - 1 : null;
	}
	
	/**
	 * Get the preivous page url, null if not
	 *
	 * @param boolean $absolute Indicate if the URI should be absolute
	 * @return string|not
	 */
	public function getPreviousUrl($absolute = false) {
		return $this->hasPrevious() ? $this->getUrl($this->getPrevious(), $absolute) : null;
	}
	
	/**
	 * Indicates if there is a next page
	 * 
	 * @return boolean
	 */
	public function hasNext() {
		return $this->curPage < $this->nbPages;
	}
	
	/**
	 * Get the next page number, null if not
	 * 
	 * @return int|null
	 */
	public function getNext() {
		return $this->hasNext() ? $this->curPage + 1 : null;
	}
	
	/**
	 * Get the next page url, null if not
	 *
	 * @param boolean $absolute Indicate if the URI should be absolute
	 * @return string|not
	 */
	public function getNextUrl($absolute = false) {
		return $this->hasNext() ? $this->getUrl($this->getNext(), $absolute) : null;
	}
	
	/**
	 * Get the last page number
	 *
	 * @return int 
	 */
	public function getLast() {
		return $this->getNbPages();
	}
	
	/**
	 * Get the last page URL
	 *
	 * @param boolean $absolute Indicate if the URI should be absolute
	 * @return string
	 */
	public function getLastUrl($absolute = false) {
		return $this->getUrl($this->getLast(), $absolute);
	}
	
	/**
	 * Get Pages index URLs 
	 *
	 * @param int $nb Number of pages to be shown in the index
	 * @param boolean $absolute Indicate if the URI should be absolute
	 * @return array
	 */
	public function getPagesIndex($nb = 11, $absolute = false) {
		$space = ($nb - 1) / 2;
		$start = $this->getCurPage() - $space;
		$end = $this->getCurPage() + $space;
		if ($start < 1 && $end > $this->getNbPages()) {
			$start = 1;
			$end = $this->getNbPages();
		} else if ($start < 1) {
			$start = 1;
			$end = $nb - 1;
		} else if ($end > $this->getNbPages()) {
			$end = $this->getNbPages();
			$start = $end - $nb + 1;
		}
		
		if ($start < 1)
			$start = 1;
		if ($end > $this->getNbPages())
			$end = $this->getNbPages();
		
		$ret = array();
		for($i = $start; $i <= $end; $i++) {
			$ret[$i] = array(
				$this->getUrl($i, $absolute),
				$i == $this->getCurPage()
			);
		}
		return $ret;
	}
	
	/**
	 * Calculates the number of pages 
	 */
	protected function calcNbPages() {
		if (!is_null($this->nbPerPage) && !is_null($this->nbResults))
			$this->nbPages = ceil($this->nbResults / $this->nbPerPage);
	}
	
	/**
	 * Get the start element to use when fetchings objects
	 * 
	 * @return int
	 */
	public function getStart() {
		return ($this->curPage - 1) * $this->nbPerPage;
	}
	
	/**
	 * Get the number of pages
	 * 
	 * @return int
	 */
	public function getNbPages() {
		return $this->nbPages;
	}
	
	/**
	 * Get the route parameters
	 *
	 * @return array
	 */
	public function getRoutePrm() {
		return $this->routePrm;
	}

	/**
	 * Set the route parameters
	 *
	 * @param array $routePrm 
	 */
	public function setRoutePrm(array $routePrm) {
		$this->routePrm = $routePrm;
	}
	
	/**
	 * Get the service
	 *
	 * @return NyroDev\UtilityBundle\Services\MainService
	 */
	public function getService() {
		return $this->service;
	}

	/**
	 * Set the router
	 *
	 * @param NyroDev\UtilityBundle\Services\MainService $service 
	 */
	public function setService(MainService $service) {
		$this->service = $service;
	}

	/**
	 * Get the route name
	 * 
	 * @return string
	 */
	public function getRoute() {
		return $this->route;
	}

	/**
	 * Set the route name
	 *
	 * @param string $route 
	 */
	public function setRoute($route) {
		$this->route = $route;
	}

	/**
	 * Get the current page
	 * 
	 * @return int
	 */
	public function getCurPage() {
		return $this->curPage;
	}

	/**
	 * Set the current page
	 * 
	 * @param int $curPage 
	 */
	public function setCurPage($curPage) {
		$this->curPage = $curPage;
	}

	/**
	 * Get the number of elements per page
	 * 
	 * @return int
	 */
	public function getNbPerPage() {
		return $this->nbPerPage;
	}

	/**
	 * Set the number of elements per page
	 * 
	 * @param int $nbPerPage 
	 */
	public function setNbPerPage($nbPerPage) {
		$this->nbPerPage = $nbPerPage;
		$this->calcNbPages();
	}

	/**
	 * Get the number of results
	 * 
	 * @return int
	 */
	public function getNbResults() {
		return $this->nbResults;
	}

	/**
	 * Set the number of results
	 * 
	 * @param int $totPages 
	 */
	public function setNbResults($nbResults) {
		$this->nbResults = $nbResults;
		$this->calcNbPages();
	}

}