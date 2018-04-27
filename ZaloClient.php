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

    /**
     * Get all follower ids
     */
    protected function getFollowerIDs($offset = 0, $count = 50) {
        $data = [
            'offset' => $offset,
            'count' => $count
        ];

        $params = ['data' => $data];

        return $followerIds = $this->zalo->get(ZaloEndpoint::API_OA_GET_FOLLOWERS_LIST, $params)->getDecodedBody()['data']['followers'];
    }

    /**
     * Send message to user
     * $uid: User id want to send message
     * $message: Message want to send
     */
    public function sendMessage($uid, $message)
    {
        $data = [
            'uid' => $uid,
            'message' => $message
        ];

        $params = ['data' => $data];

        $response = $this->zalo->post(ZaloEndpoint::API_OA_SEND_TEXT_MSG, $params);

        $result = $response->getDecodedBody();
    }

    /**
     * Broadcast message
     * $message (String): The message need to be broadcasted
     */
    public function broadcast($message) {
        $followers = $this->getFollowerIDs();

        foreach ($followers as $follower) {
            $uid = $follower['uid'];

            $this->sendMessage($uid, $message);
        }
    }


    /**
     * Get Orders info
     * filter value can pass to
     * 0	Get all orders
     * 1	Get the new orders
     * 2	Get the processing orders
     * 3	Get the verified orders
     * 4	Get the deliverying orders
     * 5	Get the deliveried orders
     * 6	Get the canceled orders
     * 7	Get the failed delivery orders
     */
    public function getOrders($offset = 0, $count = 50, $filter = 0)
    {
        $data = [
            'offset' => $offset,
            'count' => $count,
            'filter' => $filter
        ];

        $params = ['data' => $data];

        return $this->zalo->get(ZaloEndpoint::API_OA_STORE_GET_SLICE_ORDER, $params)->getDecodedBody()['data'];
    }


    /**
     * Get Order info
     */ 
    public function getOrder($orderID)
    {
        $params = ['data' => $orderID];
        return $this->zalo->get(ZaloEndpoint::API_OA_STORE_GET_ORDER, $params)->getDecodedBody()['data'];
    }

    /**
     * Update order
     */
    public function updateOrder($orderID, $status, $reason='', $cancelReason)
    {
        $data = [
            'orderid' => $orderID,
            'status' => $status,
            'reason' => $reason,
            'cancelReason' => $cancelReason
        ];

        $params = ['data' => $data];

        return $this->zalo->get(ZaloEndpoint::API_OA_STORE_UPDATE_ORDER, $params)->getDecodedBody()['data'];
    }

    /**
     * Get categories
     * $count max = 10 
     */
    public function getCategories($offset = 0, $count = 10)
    {
        $data = [
            'offset' => $offset,
            'count' => $count
        ];

        $params = ['data' => $data];

        return $this->zalo->get(ZaloEndpoint::API_OA_STORE_GET_SLICE_CATEGORY, $params)->getDecodedBody()['data'];
    }

    /**
     * Create new category
     * $name: name of caterory, max = 50 chars
     * $desc: description of category, max = 1000 chars
     * $photo: id photo of category, get from upload category photo api
     * $status: the status of category, 0: show; 1: hide
     * return id of new category just created
     */
    public function createCategory($name, $desc, $photo, $status)
    {
        $data = [
            'name' => $name,
            'desc' => $desc, 
            'photo' => $photo,
            'status' => $status
        ];

        $params = ['data' => $data];

        return $this->zalo->get(ZaloEndpoint::API_OA_STORE_CREATE_CATEGORY, $params)->getDecodedBody()['data'];
    }

    /**
     * Update the category
     * $categoryid: the id of category need to update
     * $category: the association array of category new info, format like create category api
     */
    public function updateCategory($categoryid, $category)
    {
        $data = [
            'categoryid' => $categoryid,
            'category' => $category
        ];

        $params = ['data' => $data];

        return $this->zalo->get(ZaloEndpoint::API_OA_STORE_UPDATE_CATEGORY, $params)->getDecodedBody()['data'];
    }

    /**
     * Upload category photo
     * $filePath: file path to the photo
     * return id of photo ["categoryId": 1]
     */
    public function uploadCategoryPhoto($filePath)
    {     
        $params = ['file' => new ZaloFile($filePath)];
        return $zalo->post(ZaloEndpoint::API_OA_STORE_UPLOAD_CATEGORY_PHOTO, $params)['data'];
    }

    /**Upload photo
     * $filePath: dường dẫ đến hình ảnh
     */
    protected function uploadPhoto($filePath){

        $params = ['file' => new ZaloFile($filePath)];
        $response = $this->zalo->post(ZaloEndpoint::API_OA_STORE_UPLOAD_PRODUCT_PHOTO, $params);
        return $result = $response->getDecodedBody()['data']['imageId'];     
        
    }

    /**Create product
     * $filePaths: danh sách đướng đẫ đến hình ảnh
     * $cateids:
     * $name: tên sản phẩm
     * $desc: mô tả sản phẩm        
     * $code:mã sản phẩm. chỉ gồm số và chữ
     * $price: giá sản phẩm
     * $photos: dánh sách hình ảnh tối đa 10 hình kích thước tối thiểu 500*500
     * $display: trạng thái sant phẩm show/hide
     * $payment: 2 - enable | 3 - disable
     */
    public function createProduct($cateids,$name,$desc,$code,$price= 15000,$filePaths,$display='show',$payment= 2){
        $photos = [];
        foreach($filePaths as $filePath){      
            $photo=array('id'=>$this->uploadPhoto($filePath));
            $photos[] = $photo;
        }
        $data = array(  
            'cateids' => $cateids,                
            'name' => $name,
            'desc' => $desc,
            'code' => $code,
            'price' => $price,
            'photos' => $photos,
            'display' => $display, // show | hide
            'payment' => $payment // 2 - enable | 3 - disable
        );
        $params = ['data' => $data];
        $response = $this->zalo->post(ZaloEndpoint::API_OA_STORE_CREATE_PRODUCT, $params);
        return $result = $response->getDecodedBody()['data']; // result 
    }

    /**
     * Update variation
     * $variationid: variation ID
     * $default: trạng thái 1 (enable), 2 (disable)
     * $price:chêch lệch giá
     * $name: tên
     * $status:  2: Enable, 3: Disable
     */
    public function updateVarition($variationid,$default=1,$price=0,$name,$status=2){

        $variation = array(
            'variationid' => $variationid,
            'default' => $default, // 1 (enable), 2 (disable)
            'price' => $price,
            'name' => $name,
            'status' => $status // 2: Enable, 3: Disable
        );
        $data = array(
            'variation' => $variation
        );
        $params = ['data' => $data];
        $response = $zalo->post(ZaloEndpoint::API_OA_STORE_UPDATE_VARIATION, $params);
        return $result = $response->getDecodedBody()['data'];

    }

    /**
     * add variation
     * $productID: id sản phẩm
     * $variations: danh sách danh mục tối đa 30
     * 'default':int ,  xác định variation mặc định, 1 (enable), 2 (disable)
     * 'price':
     * 'name':tên
     * 'code':thuộc tính tùy chọn, mã variation
     * 'status':2: Enable, 3: Disable
     * 'attributes': 
     */
    public function addVariation($productID,$variations){

        // $variationOne = array(
        //     'default' => 1, // 1 (enable), 2 (disable)
        //     'price' => 4,
        //     'name' => "put_variation_name_here",
        //     'attributes' => ["put_attribute_id_x1_here", "put_attribute_id_x2_here", "put_attribute_id_x3_here", "put_attribute_id_x4_here"]
        // );
        // $variationTwo = array(
        //     'default' => 2,
        //     'price' => 5,
        //     'name' => "put_variation_name_here",
        //     'attributes' => ["put_attribute_id_y1_here", "put_attribute_id_y2_here", "put_attribute_id_y3_here", "put_attribute_id_y4_here"]
        // );
        $data = array(
            'productid' => "put_product_id_here",
            'variations' => $variations
        );
        $params = ['data' => $data];
        $response = $zalo->post(ZaloEndpoint::API_OA_STORE_ADD_VARIATION, $params);
        return $result = $response->getDecodedBody()['data'];
    }

    /**
     * Get Attribute Info
     * $attributeIDs: Danh sách id các thuộc tính
     */
    public function getAttributeInfo($attributeIDs){
        $data = array(
            'attributeids' => $attributeIDs
        );
        $params = ['data' => $data];
        $response = $zalo->get(ZaloEndpoint::API_OA_STORE_GET_ATTRIBUTE_INFO, $params);
        return $result = $response->getDecodedBody()['data'];
    }


    /**
     * Get list attribute
     * $offset: vị trí bắt đầu
     * $count: số lượng sản phẩm
     */
    public function getListAttribute($offset=0,$count=10){
        $data = array(
            'offset' => $offset,
            'count' => $count
        );
        $params = ['data' => $data];
        $response = $zalo->get(ZaloEndpoint::API_OA_STORE_GET_SLICE_ATTRIBUTE, $params);
        return $result = $response->getDecodedBody()['data'];
    }

    /**
     * Update attribute
     * $attributeid: ID thuộc tính
     * $name: tên thuộc tính
     */
    public function updateAttribute($attributeID,$name){
        $data = array(
            'attributeid' => $attributeID,
            'name' => $name
        );
        $params = ['data' => $data];
        $response = $zalo->post(ZaloEndpoint::API_OA_STORE_UPDATE_ATTRIBUTE, $params);
        return $result = $response->getDecodedBody()['data'];
    }

    /**
     * Create attribute
     * $name: tên thuộc tính
     * $type: Id của kiểu thuộc tính
     */
    public function createAttribute($name,$type){
        $data = array(
            'name' => $name,
            'type' => $type // get from end point -> ZaloEndpoint::API_OA_STORE_GET_SLICE_ATTRIBUTE_TYPE
        );
        $params = ['data' => $data];
        $response = $zalo->post(ZaloEndpoint::API_OA_STORE_CREATE_ATTRIBUTE, $params);
        return $result = $response->getDecodedBody()['data'];
    }

    /**
     * Get list attribute type
     * $offset: vị trí bắt đầu
     * $count: số lượng cần lấy
     */
    public function getListAttributeType($offset=0,$count=10){
        $data = array(
            'offset' => $offset,
            'count' => $count
        );
        $params = ['data' => $data];
        $response = $zalo->get(ZaloEndpoint::API_OA_STORE_GET_SLICE_ATTRIBUTE_TYPE, $params);
        return $result = $response->getDecodedBody()['data'];
    }

    /**
     * Update product
     * $cateids:
     * $name: tên sản phẩm
     * $desc: mô tả sản phẩm        
     * $code:mã sản phẩm. chỉ gồm số và chữ
     * $price: giá sản phẩm
     * $photos: dánh sách hình ảnh tối đa 10 hình kích thước tối thiểu 500*500
     * $display: trạng thái sant phẩm show/hide
     * $payment: 2 - enable | 3 - disable
     */
    public function updateProduct($productID,$cateids,$name,$desc,$code,$price= 15000,$filePaths,$display='show',$payment= 2){
        $photos = [];
        foreach($filePaths as $filePath){      
            $photo=array('id'=>$this->uploadPhoto($filePath));
            $photos[] = $photo;
        }
        $productUpdate = array(
            'cateids' => $cateids,                
            'name' => $name,
            'desc' => $desc,
            'code' => $code,
            'price' => $price,
            'photos' => $photos,
            'display' => $display, // show | hide
            'payment' => $payment // 2 - enable | 3 - disable
        );
        $data = array(
            'productid' => $productID,
            'product' => $productUpdate
        );
        $params = ['data' => $data];
        $response = $zalo->post(ZaloEndpoint::API_OA_STORE_ONBEHALF_UPDATE_PRODUCT, $params);
        return $result = $response->getDecodedBody()['data']; // result
    }

    /**
     * Get list  product
     * $offset: vị trí bắt đầu
     * $count: số lượng sản phẩm 
     */
    public function getListProduct($offset=0,$count=10){
        $data = array(
            'offset' => $offset,
            'count' => $count
        );
        $params = ['data' => $data];
        $response = $this->$zalo->get(ZaloEndpoint::API_OA_STORE_ONBEHALF_GET_SLICE_PRODUCT, $params);
        return $result = $response->getDecodedBody()['data']; // result
    }

    /**
     * Remove product
     * $productID: id sản phẩm
     */
    public function removeProduct($productID){
        $data = array(
            'productid' => $productID
        );
        $params = ['data' => $data];
        $response = $this-> $zalo->post(ZaloEndpoint::API_OA_STORE_ONBEHALF_REMOVE_PRODUCT, $params);
        return $result = $response->getDecodedBody()['data'];
    }

}

