<?php

namespace Shippop\Ecommerce\Plugins;

use Magento\Framework\App\Action\AbstractAction;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Session\SessionManagerInterface as CoreSession;

class StoreSessionVar
{
    protected $_coreSession;

    public function __construct(
        CoreSession $coreSession
    ) {
        $this->_coreSession = $coreSession;
    }

    public function beforeDispatch(
        AbstractAction $subject,
        RequestInterface $request
    ) {
        $this->_coreSession->start();
        $this->_coreSession->setParcelDelivery([
            "drop_off" => ["THP", "TP2", "JNTD"],
            "pick_up" => ["APF", "KRY", "RSB", "SKT", "SCG", "SCGC", "SCGF", "NJV", "LLM", "CJE", "FLE", "JNTP"]
        ]);
        $this->_coreSession->setParcelLogo([
            "APF" => "logistic_logo/APF.png",
            "BEE" => "logistic_logo/BEE.png",
            "ARM" => "logistic_logo/ARM.png",
            "CJE" => "logistic_logo/CJLX.png",
            "CJLX" => "logistic_logo/CJLX.png",
            "CJL" => "logistic_logo/CJLX.png",
            "FLE" => "logistic_logo/FLE.png",
            "JNTP" => "logistic_logo/JNTP.png",
            "JNTPF" => "logistic_logo/JNTP.png",
            "JNTD" => "logistic_logo/JNTP.png",
            "JWDC" => "logistic_logo/JWDC.png",
            "JWDF" => "logistic_logo/JWDC.png",
            "LLM" => "logistic_logo/LLM.png",
            "NJV" => "logistic_logo/NJV.png",
            "NJVE" => "logistic_logo/NJV.png",
            "SCG" => "logistic_logo/SCG.png",
            "SCGC" => "logistic_logo/SCGC.png",
            "SCGF" => "logistic_logo/SCGC.png",
            "SEN" => "logistic_logo/SEN.png",
            "THP" => "logistic_logo/THP.png",
            "TP2" => "logistic_logo/TP2.png",
            "SKT" => "logistic_logo/SKT.png",
            "KRY" => "logistic_logo/KRY.png",
            "KRYP" => "logistic_logo/KRY.png",
            "KRYD" => "logistic_logo/KRY.png",
            "BEST" => "logistic_logo/BEST.png",
            "TRUE" => "logistic_logo/TRUE.png",
            "SPE" => "logistic_logo/SPE.png",

            "ZPT" => "logistic_logo/ZPT.png",
            "ZPTE" => "logistic_logo/ZPT.png",
            "SKY" => "logistic_logo/SKY.png",
            "POS" => "logistic_logo/POS.png",
            "POSD" => "logistic_logo/POS.png",
            "DHL" => "logistic_logo/DHL.png",
            "NTW" => "logistic_logo/NTW.jpg",

            "SPE" => "logistic_logo/SPE.png"
        ]);

        $this->_coreSession->setOnDemand(["LLM", "SKT"]);
        $this->_coreSession->setShippopStatus([
            "wait" => __("Pending"),
            "booking" => __("Confirmed"),
            "shipping" => __("During delivery"),
            "complete" => __("Success"),
            "cancel" => __("Failed/Cancelled"),
            "return" => __("Returned")
        ]);
        $this->_coreSession->setShippopCodStatus([
            "wait_transfer" => __("Confirmation pending"),
            "pending_transfer" => __("Pending transfer"),
            "transferred" => __("Transfered"),
            "cancel_transfer" => __("Cancelled")
        ]);
        $this->_coreSession->setLabelSize([
            "receipt" => __("Receipt ( Default )"),
            "A4" => "A4",
            "A5" => "A5",
            "A6" => "A6",
            "letter" => __("Letter"),
            "letter4x6" => __("Size 4x6"),
            "sticker" => __("Sticker size 8x8 cm"),
            "sticker4x6" => __("Sticker size 4x6 in")
        ]);
    }
}
