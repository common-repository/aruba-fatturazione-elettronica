<?php
namespace ArubaFe\Admin\Documents;
if (!defined('ABSPATH')) die('No direct access allowed');

use ArubaFe\Admin\RestApi\Interfaces\ArubaFeApiResponseInterface;

class ArubaFeDocument
{

    private $wp_filesystem;

    public function __construct() {
        global $wp_filesystem;

        if (!function_exists('request_filesystem_credentials')) {
            require_once(ABSPATH . '/wp-admin/includes/file.php');
        }

        ob_start();
        $credentials = request_filesystem_credentials( '' );
        ob_end_clean();

        if ( false === $credentials || ! WP_Filesystem( $credentials ) ) {
            return new WP_Error( 'fs_unavailable', __( 'Could not access filesystem.' ) );
        }

        $this->wp_filesystem = $wp_filesystem;

    }

    public function download(ArubaFeApiResponseInterface $response){

        $json        = $response->getJSON();
        $fileName    = sanitize_file_name( $json->fileName );
        $fileData    = $json->content;
        $fileBinary  = base64_decode( $fileData );
        $fileSize    = strlen( $fileBinary );
        $contentType = sanitize_mime_type($json->contentType);

        $tmpFileName = wp_tempnam('aruba-fe-tmp-doc');

        $this->wp_filesystem->put_contents($tmpFileName,$fileBinary);

        header( "Content-Type: $contentType" );
        header( "Content-Disposition: attachment; filename=\"$fileName\"" );
        header( "Content-Length: $fileSize" );

        require ($tmpFileName);

        exit();

    }

    public function writeAttachment($fileName,$fileContents){

        $fileName = sanitize_file_name($fileName);
        $path = ARUBA_FE_PATH . 'tmp_invoices/' . $fileName;

        if($this->wp_filesystem->put_contents($path ,$fileContents)){
            return $path;
        }

        return false;

    }


}