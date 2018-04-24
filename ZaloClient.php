<?php 

use Zalo\Zalo;
use Zalo\ZaloEndpoint;
use Zalo\FileUpload\ZaloFile;

class ZaloClient {
    protected $zaloConfig;
    protected $zalo;

    public function __construct(ZaloConfig $zaloConfig) {
        $this->zaloConfig = $zaloConfig;
        $this->zalo = new Zalo($this->zaloConfig->getConfig());
    }

    /** gets follower ID*/
    protected function getFollowerIDs() {
        $data = [
            'offset' => 0,
            'count' => 50
        ];

        $params = ['data' => $data];

        return $followerIds = $this->zalo->get('/getfollowers', $params)->getDecodedBody()['data']['followers'];
    }

    /** send message to all follower
     * $mesage
     */
    public function sendMessageToAll($message) {
        $followerIds = $this->getFollowerIDs();


        foreach ($followerIds as $follower) {
            $data = [
                'uid' => $follower['uid'],
                'message' => $message
            ];

            $params = ['data' => $data];
            
            $response = $this->zalo->post(ZaloEndpoint::API_OA_SEND_TEXT_MSG, $params);

            $result = $response->getDecodedBody();
        }
        
    }

    /**Upload photo
     * $filePath
     */
    protected function uploadPhoto($filePath){

        $params = ['file' => new ZaloFile($filePath)];
        $response = $this->zalo->post(ZaloEndpoint::API_OA_STORE_UPLOAD_PRODUCT_PHOTO, $params);
        return $result = $response->getDecodedBody();
        
    }

    /**Create product
     * $filePath
     * $name
     */
    public function createProduct($filePaths,$name){

        // $cate = array('cateid' => 'put_your_cate_id_here');
        // $cates = [$cate];
        // $photo = array('id' => 'put_your_image_id_here');
        // $photos = [$photo];
        $photos=[];
        var_dump($this->uploadPhoto($filePaths));
        die();
        foreach($filePaths as $filePath){
            
            $photos[]=uploadPhoto($filePath);
            
        }
        $data = array(
            'cateids' => $cates,
            'name' => 'put_your_product_name_here',
            'desc' => 'put_your_description_here',
            'code' => 'put_your_code_number_here',
            'price' => 15000,
            'photos' => $photos,
            'display' => 'show', // show | hide
            'payment' => 2 // 2 - enable | 3 - disable
        );
        $params = ['data' => $data];
        $response = $zalo->post(ZaloEndpoint::API_OA_STORE_CREATE_PRODUCT, $params);
        $result = $response->getDecodedBody(); // result

    }
}
