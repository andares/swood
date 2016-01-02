<?php
namespace Swood;
use Swood\Debug as D;

/**
 * Swood Runtime
 *
 * @author andares
 */
trait Runtime {

    /**
     *
     * @var \swoole_server|\swoole_client
     */
    public $swoole;

    public function packData($data) {
        $setting = $this->swoole->setting;

        if (isset($setting['open_length_check']) && $setting['open_length_check']) {
            $data = pack($setting['package_length_type'], strlen($data)) . $data;
        } elseif (isset($setting['open_eof_check']) && $setting['open_eof_check']) {
            $data .= $setting['package_eof'];
        }
        return $data;
    }

    public function unpackData($data) {
        $setting = $this->swoole->setting;

        if (isset($setting['open_length_check']) && $setting['open_length_check']) {
            $pack_data = unpack($setting['package_length_type'], $data);
            if ($pack_data) {
                $length = intval($pack_data[1]);
            } else {
                return false;
            }
            $data   = substr($data, $setting['package_body_offset'], $length);
        } elseif (isset($setting['open_eof_check']) && $setting['open_eof_check']) {
            $pos    = strpos($data, $setting['package_eof'], $setting['package_body_offset']);
            $data   = substr($data, $setting['package_body_offset'],
                $pos - $setting['package_body_offset']);
        }
        return $data;
    }

    public static function raiseError($errno, $errstr, $errfile, $errline, $errcontext = []) {
        if (intval(ini_get('log_errors'))) {
            error_log("PHP Error[$errno]: $errstr in $errfile on line $errline", 0);
        }
        throw new \PhpError($errstr, 0, $errno, $errfile, $errline, null);
    }

    public static function raiseException(\Exception $exception) {
        D::ec("Uncaught exception: " . $exception->getMessage());
        if (\Dev\Debug::getMode()) {
            D::ec($exception->getTraceAsString());
        }
    }

}
