<?php

namespace MailCampaigns\SubscriberApi\Controller\Adminhtml\Info;

use Magento\Backend\App\Action;
use Magento\Framework\App\Action\HttpGetActionInterface;

/**
 * Class Index
 */
class Index extends Action implements HttpGetActionInterface
{
    public const MENU_ID = 'MailCampaigns_SubscriberApi::info';

    /**
     * MailCampaigns Subscriber API info page
     *
     * @return void
     */
    public function execute()
    {
        $this->_view->loadLayout();

        $this->_setActiveMenu(self::MENU_ID);
        $this->_view->getPage()->getConfig()->getTitle()->prepend(
            __('MailCampaigns Newsletter Subscriber API Extension')
        );

        $this->_addContent(
            $this->_view->getLayout()->createBlock(
                \MailCampaigns\SubscriberApi\Block\Adminhtml\Info\Index::class
            )
        );

        $this->_view->renderLayout();
    }
}
