<?php
class PaypalController extends CController
{
    public function actionPrepare()
    {
        $paymentName = 'paypal';

        $payum = $this->getPayum();

        $tokenStorage = $payum->getTokenStorage();
        $paymentDetailsStorage = $payum->getRegistry()->getStorageForClass(
            'Payum\Paypal\ExpressCheckout\Nvp\Model\PaymentDetails',
            $paymentName
        );

        $paymentDetails = $paymentDetailsStorage->createModel();
        $paymentDetails['PAYMENTREQUEST_0_CURRENCYCODE'] = 'USD';
        $paymentDetails['PAYMENTREQUEST_0_AMT'] = 1.23;
        $paymentDetailsStorage->updateModel($paymentDetails);

        $doneToken = $tokenStorage->createModel();
        $doneToken->setPaymentName($paymentName);
        $doneToken->setDetails($paymentDetailsStorage->getIdentificator($paymentDetails));
        $doneToken->setTargetUrl(
            $this->createAbsoluteUrl('paypal/done', array('payum_token' => $doneToken->getHash()))
        );
        $tokenStorage->updateModel($doneToken);

        $captureToken = $tokenStorage->createModel();
        $captureToken->setPaymentName('paypal');
        $captureToken->setDetails($paymentDetailsStorage->getIdentificator($paymentDetails));
        $captureToken->setTargetUrl(
            $this->createAbsoluteUrl('payment/capture', array('payum_token' => $captureToken->getHash()))
        );
        $captureToken->setAfterUrl($doneToken->getTargetUrl());
        $tokenStorage->updateModel($captureToken);

        $paymentDetails['RETURNURL'] = $captureToken->getTargetUrl();
        $paymentDetails['CANCELURL'] = $captureToken->getTargetUrl();
        $paymentDetailsStorage->updateModel($paymentDetails);

        $this->redirect($captureToken->getTargetUrl());
    }

    public function actionDone()
    {
        $token = $this->getPayum()->getHttpRequestVerifier()->verify($_REQUEST);
        $payment = $this->getPayum()->getRegistry()->getPayment($token->getPaymentName());

        $payment->execute($status = new \Payum\Request\BinaryMaskStatusRequest($token));

        $content = '';
        if ($status->isSuccess()) {
            $content .= '<h3>Payment status is success.</h3>';
        } else {
            $content .= '<h3>Payment status IS NOT success.</h3>';
        }

        $content .= '<br /><br />'.json_encode(iterator_to_array($status->getModel()), JSON_PRETTY_PRINT);

        echo '<pre>',$content,'</pre>';
        exit;
    }

    /**
     * @return \Payum\YiiExtension\PayumComponent
     */
    private function getPayum()
    {
        return Yii::app()->payum;
    }
}