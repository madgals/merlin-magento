<?php

class Blackbird_Merlinsearch_Model_Merlin_Search extends Varien_Object
{

    protected $_search;

    public function __construct($query)
    {
        $this->_search = new \Merlin\Search($query);
        parent::__construct();
    }

    public function getSearch()
    {
        return $this->_search;
    }

    public function setNum($num)
    {
        $this->getSearch()->setNum($num);
    }

    public function setStart($start)
    {
        $this->getSearch()->setStart($start);
    }

    public function setGroup($group)
    {
        $this->getSearch()->setGroup(new \Merlin\Group($group));
    }

    public function addFilter($field, $operator, $value, $tag = null, $type = null)
    {
        $this->getSearch()->addFilter(new \Merlin\Filter($field, $operator, $value, $tag, $type));
    }

    public function addHistFacet($field, $start, $end, $gap, $key = null)
    {
        $this->getSearch()->addFacet(new \Merlin\HistFacet($field, $start, $end, $gap, $key));
    }

    public function addEnumFacet($field, $num = null, $key = null)
    {
        $this->getSearch()->addFacet(new \Merlin\EnumFacet($field, $num, $key));
    }

    public function addPagination($page, $limit)
    {
        if (!$limit) {
            $limit = 12;
        }

        $this->setNum($limit);
        if ($page > 1) {
            $this->setStart(($page - 1) * $limit);
        }
    }

    public function setOrder($orderBy, $orderDir)
    {
        if ($orderBy == 'relevance') {
            return;
        }
        if ($orderBy == 'name') {
            $orderBy = 'title';
        }
        if($orderBy && $orderDir) {
            $this->getSearch()->addSort(new \Merlin\Sort($orderBy, $orderDir));
        }
    }


}
