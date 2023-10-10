<?php

class BlogPagination {
    private $page;
    private $pagesCount;

    private $route;
    private $route_params;
    private $link;

    /**
     * @param $page
     * @param $pagesCount
     * @param $route
     * @param $route_params
     */
    public function __construct($page, $pagesCount, $route, $route_params)
    {
        $this->page = $page;
        $this->pagesCount = $pagesCount;
        $this->route = $route;
        $this->route_params = $route_params;
        
        $this->link = new Link();
    }

    /**
     * @return mixed
     */
    public function getPage()
    {
        return $this->page;
    }

    /**
     * @return mixed
     */
    public function getPagesCount()
    {
        return $this->pagesCount;
    }


    /**
     * @param int $page
     * @param string $type
     *
     * @return array
     */
    private function buildPageLink($page, $type = 'page')
    {
        $current = $page == $this->getPage();

        return [
            'type' => $type,
            'page' => $page,
            'url' => $this->link->getModuleLink("mdn_blog", $this->route, array_merge($this->route_params, ['page' => $page == 1 ? null : $page])),
            'clickable' => !$current,
            'current' => $type === 'page' ? $current : false,
        ];
    }

    /**
     * @return array
     */
    private function buildSpacer()
    {
        return [
            'type' => 'spacer',
            'page' => null,
            'clickable' => false,
            'current' => false,
        ];
    }

    /**
     * @return array
     */
    public function buildLinks()
    {
        $links = [];

        $addPageLink = function ($page) use (&$links) {
            static $lastPage = null;

            $page = round($page);

            if ($page < 1 || $page > $this->getPagesCount()) {
                return;
            }

            if (null !== $lastPage && $page > $lastPage + 1) {
                $links[] = $this->buildSpacer();
            }

            if ($page !== $lastPage) {
                $links[] = $this->buildPageLink($page);
            }

            $lastPage = $page;
        };

        $boundaryContextLength = 1;
        $pageContextLength = 3;

        $links[] = $this->buildPageLink(max(1, $this->getPage() - 1), 'previous');

        for ($i = 0; $i < $boundaryContextLength; ++$i) {
            $addPageLink(1 + $i);
        }

        $start = max(1, $this->getPage() - (int) floor(($pageContextLength - 1) / 2));
        if ($start + $pageContextLength > $this->getPagesCount()) {
            $start = $this->getPagesCount() - $pageContextLength + 1;
        }

        for ($i = 0; $i < $pageContextLength; ++$i) {
            $addPageLink($start + $i);
        }

        for ($i = 0; $i < $boundaryContextLength; ++$i) {
            $addPageLink($this->getPagesCount() - $boundaryContextLength + 1 + $i);
        }

        $links[] = $this->buildPageLink(min($this->getPagesCount(), $this->getPage() + 1), 'next');

        return $links;
    }
}