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

        return $followerIds = $this->zalo->get(ZaloEndpoint::API_OA_GET_FOLLOWERS_LIST, $params)->getDecodedBody()['data']['followers'];
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
        return $result = $response->getDecodedBody()['data']['imageId'];     
        
    }

    /**Create product
     * $filePath
     * $name
     */
    public function createProduct($cates,$filePaths,$name,$desc,$code,$price,$display,$payment){
        if (is_array($filePaths))
        {
            foreach($filePaths as $filePath){      
                $photo=array('id'=>$this->uploadPhoto($filePath));
                $photos = [$photo];
            }
        }
        $data = array(  
            'cateids' => $cates,                
            'name' => 'test',
            'desc' => 'put your description here',
            'code' => '123',
            'price' => 15000,
            'photos' => $photos,
            'display' => 'show', // show | hide
            'payment' => 2 // 2 - enable | 3 - disable
        );
        $params = ['data' => $data];
        $response = $this->zalo->post(ZaloEndpoint::API_OA_STORE_CREATE_PRODUCT, $params);
        return $result = $response->getDecodedBody(); // result 
    }
}
