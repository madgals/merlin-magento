<?php
require_once Mage::getModuleDir('controllers', 'Mage_CatalogSearch').DS.'ResultController.php';

class Blackbird_Merlinsearch_Frontend_CatalogSearch_ResultController extends Mage_CatalogSearch_ResultController
{
    const ORDER_BY_GET_PARAM_NAME = 'order';
    const ORDER_DIR_GET_PARAM_NAME = 'dir';
    const PAGE_GET_PARAM_NAME = 'p';

    public function indexAction()
    {
        //Mage::log('Mage_CatalogSearch_ResultController indexAction()');

        $query = Mage::helper('catalogsearch')->getQuery();
        /* @var $query Mage_CatalogSearch_Model_Query */

        $query->setStoreId(Mage::app()->getStore()->getId());

        if ($query->getQueryText() != '') {
            if (Mage::helper('catalogsearch')->isMinQueryLength()) {
                $query->setId(0)
                    ->setIsActive(1)
                    ->setIsProcessed(1);
            } else {
                if ($query->getId()) {
                    $query->setPopularity($query->getPopularity() + 1);
                } else {
                    $query->setPopularity(1);
                }

                if ($query->getRedirect()) {
                    $query->save();
                    $this->getResponse()->setRedirect($query->getRedirect());
                    return;
                } else {
                    $query->prepare();
                }
            }

            Mage::helper('catalogsearch')->checkNotes();

            //Mage::log(print_r($query->getData(), true));
            //foreach($query->getResultCollection() as $prod) {
            //  Mage::log(print_r($prod->getData(), true));
            //}
            $layer = Mage::getSingleton('catalogsearch/layer');
            $orderBy = $this->getRequest()->getParam(self::ORDER_BY_GET_PARAM_NAME);
            $orderDir = $this->getRequest()->getParam(self::ORDER_DIR_GET_PARAM_NAME);
            $page = $this->getRequest()->getParam(self::PAGE_GET_PARAM_NAME);
            $layer->getProductCollection()->setOrder($orderBy,$orderDir);
            $layer->getProductCollection()->setCurPage($page);

            $this->loadLayout();
            $this->_initLayoutMessages('catalog/session');
            $this->_initLayoutMessages('checkout/session');
            $this->renderLayout();
            //Mage::log(Mage::getSingleton('core/layout')->getUpdate()->getHandles());

            if (!Mage::helper('catalogsearch')->isMinQueryLength()) {
                $query->save();
            }
        } else {
            $this->_redirectReferer();
        }
    }
}
