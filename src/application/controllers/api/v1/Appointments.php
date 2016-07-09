<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/* ----------------------------------------------------------------------------
 * Easy!Appointments - Open Source Web Scheduler
 *
 * @package     EasyAppointments
 * @author      A.Tselegidis <alextselegidis@gmail.com>
 * @copyright   Copyright (c) 2013 - 2016, Alex Tselegidis
 * @license     http://opensource.org/licenses/GPL-3.0 - GPLv3
 * @link        http://easyappointments.org
 * @since       v1.2.0
 * ---------------------------------------------------------------------------- */

require_once __DIR__ . '/API_V1_Controller.php';

use \EA\Engine\Api\V1\Response;
use \EA\Engine\Types\NonEmptyString; 

/**
 * Appointments Controller
 *
 * @package Controllers
 * @subpackage API
 */
class Appointments extends API_V1_Controller {
    /**
     * Appointments Resource Parser
     * 
     * @var \EA\Engine\Api\V1\Parsers\Appointments
     */
    protected $parser; 

    /**
     * Class Constructor
     */
    public function __construct() {
        parent::__construct();
        $this->load->model('appointments_model');
        $this->parser = new \EA\Engine\Api\V1\Parsers\Appointments;
    }

    /**
     * GET API Method 
     * 
     * @param int $id Optional (null), the record ID to be returned.
     */
    public function get($id = null) {
        try {
            $condition = $id !== null ? 'id = ' . $id : null;
            $appointments = $this->appointments_model->get_batch($condition); 

            if ($id !== null && count($appointments) === 0) {
                throw new \EA\Engine\Api\V1\Exception('The requested appointment record was not found!', 404, 
                        'Not Found');
            }

            $response = new Response($appointments); 
            $response->encode($this->parser)->search()->sort()->paginate()->minimize();

            if ($id !== null) {
                $response->singleEntry();
            }

            $response->output();
        } catch(\Exception $exception) {
            exit($this->_handleException($exception)); 
        }   
    }

    /**
     * POST API Method 
     */
    public function post() {
        try {
            $request = json_decode(file_get_contents('php://input'), true); 
            $this->parser->decode($request); 
            $id = $this->appointments_model->add($request);
            $appointments = $this->appointments_model->get_batch('id = ' . $id); 
            $response = new Response($appointments); 
            $status = new NonEmptyString('201 Created');
            $response->encode($this->parser)->singleEntry()->output($status); 
        } catch(\Exception $exception) {
            exit($this->_handleException($exception)); 
        }  
    }

    /**
     * PUT API Method 
     *
     * @param int $id The record ID to be updated.
     */
    public function put($id) {
        try {
            $appointment = $this->appointments_model->get_batch('id = ' . $id); 

            if ($id !== null && count($appointments) === 0) {
                throw new \EA\Engine\Api\V1\Exception('The requested appointment record was not found!', 404, 
                        'Not Found');
            }
            
            $request = json_decode(file_get_contents('php://input'), true); 
            $this->parser->decode($request, $appointment); 
            $request['id'] = $id; 
            $id = $this->appointments_model->add($request);
            $appointments = $this->appointments_model->get_batch('id = ' . $id); 
            $response = new Response($appointments); 
            $status = new NonEmptyString('201 Created');
            $response->encode($this->parser)->singleEntry()->output($status); 
        } catch(\Exception $exception) {
            exit($this->_handleException($exception)); 
        }  
    }

    /**
     * DELETE API Method 
     *
     * @param int $id The record ID to be deleted.
     */
    public function delete($id) {
        try {
            $result = $this->appointments_model->delete($id);

            $response = new Response([
                'code' => 200, 
                'message' => 'Appointment was deleted successfully!'
            ]);

            $response->output();

        } catch(\Exception $exception) {
            exit($this->_handleException($exception)); 
        }  
    }
}

/* End of file Appointments.php */
/* Location: ./application/controllers/api/v1/Appointments.php */