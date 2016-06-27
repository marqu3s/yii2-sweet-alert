<?php

namespace marqu3s\sweetalert;

use Yii;
use yii\base\Widget;
use yii\helpers\Json;
use yii\web\View;

/**
 * Alert widget renders a message from session flash or custom messages.
 */
class Alert extends Widget
{
    /**
     * All flash messages stored in the session are displayed and removed
     * from the session. Default true.
     *
     * @var bool
     */
    public $useSessionFlash = true;

    /** @var bool If set to true, the user can dismiss the modal by clicking outside it. */
    public $allowOutsideClick = true;

    /** @var int Auto close timer of the modal. Set in ms (milliseconds). default - 2,0 seconds */
    public $timer = 2000;

    /** @var array Plugin options */
    public $options = [];

    /** @var string Text for the cancel button on confirmation alerts. */
    public $cancelButtonText = 'Cancelar';

    /** @var string Color in hex format for the confirm button. */
    public $confirmButtonColor = '#2196F3';

    /** @var bool If the confirmation button should be displayed. */
    public $showConfirmButton = false;


    /**
     * Render alert
     * @return string|void
     */
    public function run()
    {
        $this->registerAssets();

        # Normally session messages are informative. There is no need for a cancel button.
        if ($this->useSessionFlash) {
            $session = Yii::$app->getSession();
            $flashes = $session->getAllFlashes();

            foreach ($flashes as $type => $data) {
                $data = (array)$data;
                foreach ($data as $message) {
                    $this->options['type'] = $type;
                    $this->options['text'] = $message;
                    if ($type == 'info' || $type == 'warning') {
                        $this->options['title'] = 'Atenção!';
                        $this->timer = null;
                        $this->showConfirmButton = true;
                    } elseif ($type == 'error') {
                        $this->options['title'] = 'Oooops!';
                        $this->timer = null;
                        $this->showConfirmButton = true;
                    } else {
                        $this->options['title'] = 'Sucesso';
                        $this->showConfirmButton = false;
                    }
                }
                $session->removeFlash($type);
                $this->registerJs();
            }
        }
    }

    /**
     * Register client assets.
     * If there is a title set, register the javascript code required to show an alert on page load.
     */
    protected function registerAssets()
    {
        AlertAsset::register($this->getView());

        if (isset($this->options['title'])) {
            $this->registerJs();
        }
    }

    /**
     * Register the javascript code required to show an alert on page page load.
     */
    protected function registerJs()
    {
        $js = 'swal(' . $this->getOptions() . ');';
        $this->getView()->registerJs($js, View::POS_END);
    }

    /**
     * Get plugin options
     * @return string
     */
    public function getOptions()
    {
        $this->options['allowOutsideClick'] = $this->allowOutsideClick;
        $this->options['timer'] = $this->timer;
        $this->options['cancelButtonText'] = $this->cancelButtonText;
        $this->options['confirmButtonColor'] = $this->confirmButtonColor;
        $this->options['showConfirmButton'] = $this->showConfirmButton;
        
        return Json::encode($this->options);
    }
}
