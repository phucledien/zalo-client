<?php 

use Zalo\Zalo;
use Zalo\ZaloEndpoint;

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

}