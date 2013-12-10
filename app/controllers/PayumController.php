<?php
class PayumController extends CController
{
    public function actionIndex()
    {
        echo <<<HTML
<html>
    <body>
        <ul>
            <li><a href="index.php?r=paypal/prepare">Paypal</a></li>
            <li><a href="index.php?r=paypalActiveRecordStorage/prepare">Paypal. Active Record Storage</a></li>
        </ul>
    </body>
</html>
HTML;

        Yii::app()->end();
    }
}