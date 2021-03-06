<?php
namespace application\controllers;
use application\libs\application;

use PDO;

class FeedController extends Controller{
    public function index(){
        $this->addAttribute(_JS, ["feed/index", "https://unpkg.com/swiper@8/swiper-bundle.min.js"]);
        $this->addAttribute(_CSS, ["feed/index", "https://unpkg.com/swiper@8/swiper-bundle.min.css"]);
        $this->addAttribute(_MAIN, $this->getView("feed/index.php"));
        return "template/t1.php";
    }

    public function rest(){
        switch(getMethod()){
            case _POST:
                // if(is_array($_FILES)){
                //     foreach($_FILES['imgs']['name'] as $key => $value){
                //         print "key : {$key}, value : {$value} <br>";
                //     }
                // }
                // print "ctnt : " . $_POST["ctnt"] . "<br>";
                // print "location : " . $_POST["location"] . "<br>";
                if(!is_array($_FILES) || !isset($_FILES["imgs"])){
                    return ["result" => 0];
                }
                $param = [
                    "location" => $_POST["location"],
                    "ctnt" => $_POST["ctnt"],
                    "iuser" => getIuser()
                ];
                $ifeed = $this->model->insFeed($param);

                foreach($_FILES["imgs"]["name"] as $key => $originFileNm){
                    // $file_name = explode(".", $value);
                    // $ext = end($file_name);
                    $saveDirectory = _IMG_PATH . "/feed/" . $ifeed;
                    if(!is_dir($saveDirectory)){
                        mkdir($saveDirectory, 0777, true);
                    }
                    $tempName = $_FILES["imgs"]["tmp_name"][$key];
                    $randomFileNm = getRandomFileNm($originFileNm);
                    $param = [
                        "ifeed" => $ifeed,
                        "img" => $randomFileNm
                    ];
                    if(move_uploaded_file($tempName, $saveDirectory . "/" . $randomFileNm)){
                        // chmod("C:/Apach24/PHPgram/static/img/profile/1/test." . $ext, 0755);
                        $this->model->insFeedImg($param);
                    }
                }
                $param2 = ["ifeed" => $ifeed];
                $data = $this->model->selFeedAfterReg($param2);
                $data->imgList = $this->model->selFeedImgList($param2);
                return $data;

            case _GET:
                $page = 1;
                if(isset($_GET["page"])){
                    $page = intVal($_GET["page"]);
                }
                $startIdx = ($page - 1) * _FEED_ITEM_CNT;
                $param = [
                    "startIdx" => $startIdx,
                    "iuser" => getIuser()
                ];
                $list = $this->model->selFeedList($param);
                foreach($list as $item){
                    $param2 = ["ifeed" => $item->ifeed];
                    $item->imgList = $this->model->selFeedImgList($param2);
                    $item->cmt = Application::getModel("feedcmt")->selFeedCmt($param2);
                };
                return $list;
        }
    }

    public function fav(){
        $urlPath = getUrlPaths();
        if(!isset($urlPath[2])){
            exit();
        }
        $param = [
            "iuser" => getIuser(),
            "ifeed" => intval($urlPath[2])
        ];

        switch(getMethod()){
            case _POST:
                return [_RESULT => $this->model->insFeedFav($param)];
            case _DELETE:
                return [_RESULT => $this->model->delFeedFav($param)];
        }
    }
}