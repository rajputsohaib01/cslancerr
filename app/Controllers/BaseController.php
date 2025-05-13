<?php
namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;
use App\Models\SettingModel;
use Config\Services;

abstract class BaseController extends Controller
{
    protected $request;
    protected $settingData;

    /**
     * @return void
     */
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
        $settingModel = new SettingModel();
        $this->settingData = $settingModel->first();
        $this->setGlobalSettings();
    }
    private function setGlobalSettings()
{
    $view = Services::renderer(); // safer than view()

    if ($view !== null) {
        $view->setVar('globalSettings', $this->settingData);
    }
}

}
