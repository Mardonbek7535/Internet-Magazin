<?php
/**
 * UniSite CMS
 *
 * @copyright   2018 Artur Zhur
 * @link    https://unisitecms.ru
 * @author    Artur Zhur
 *
 */
    

    session_start();
    define('unisitecms', true);

    include_once("./systems/config.php");   
    include_once("{$_SERVER['DOCUMENT_ROOT']}/systems/classes/UniSite.php");

    if(mirror() == true){ header("Location: ".$settings["url"]); }

    if(isset($_GET["logout"])){ setcookie('rememberme', '', 0, '/');  unset($_COOKIE['rememberme']);  unset($_SESSION['profile']); }  

    if($_SESSION['profile']["id"]) $array_data["new_message"] = getOne("select count(id) as count from uni_messages where status=0 and id_user_to=?", array(intval($_SESSION['profile']["id"])));

    if(!empty($_GET["currency"])){
        if($_GET["currency"] != "multi"){
          if(isset($getCurrency[$_GET["currency"]])){
            $_SESSION["currency"] = $_GET["currency"];
            $_SESSION["currency_sign"] = $getCurrency[$_GET["currency"]]["sign"];
          }
        }else{
           unset($_SESSION["currency"]); 
           unset($_SESSION["currency_sign"]);
        }
    }

    

    $REQUEST_URI = explode("?",REQUEST_URI);
    if($REQUEST_URI[0] != "/") $URI = explode("/", trim($REQUEST_URI[0], "/")); else $URI = array();
    

    if($settings["visible_lang_site"]){

        if($URI[0] == $settings["lang_site_default"] && count($URI) == 1) header("Location: ".$prefix_dir);

        if($URI[0]){
           $find = findOne("uni_languages","iso = ?", array($URI[0]));
           if(count($find)){ 
              
              unset($URI[0]);
              
              $_SESSION["lang"]["iso"] = $find->iso;
              $_SESSION["lang"]["name"] = $find->name;
              $_SESSION["lang"]["icon"] = URL.$image_language.$find->image;

              if(file_exists($_SERVER["DOCUMENT_ROOT"]."/lang/".$_SESSION["lang"]["iso"].".php")){
                  $languages_content = json_decode( ob_get($_SERVER["DOCUMENT_ROOT"]."/lang/".$_SESSION["lang"]["iso"].".php"), true );
              }else{
                  $languages_content = json_decode( ob_get($_SERVER["DOCUMENT_ROOT"]."/lang/".$settings["lang_site_default"].".php"), true );
              }

           }else{
             if(strpos($REQUEST_URI[0], "ajax") === false){ 
              if(!$REQUEST_URI[1]) header("Location: ".$prefix_dir.$settings["lang_site_default"].$REQUEST_URI[0]."/"); else header("Location: ".$prefix_dir.$settings["lang_site_default"].$REQUEST_URI[0]."?".$REQUEST_URI[1]);
             } else {
                if($_SESSION["lang"]["iso"]){
                  if(file_exists($_SERVER["DOCUMENT_ROOT"]."/lang/".$_SESSION["lang"]["iso"].".php")){
                      $languages_content = json_decode( ob_get($_SERVER["DOCUMENT_ROOT"]."/lang/".$_SESSION["lang"]["iso"].".php"), true );
                  }                    
                }else{
                  if(file_exists($_SERVER["DOCUMENT_ROOT"]."/lang/".$settings["lang_site_default"].".php")){
                     $languages_content = json_decode( ob_get($_SERVER["DOCUMENT_ROOT"]."/lang/".$settings["lang_site_default"].".php"), true );
                  }                    
                }
             }       
           }
        }else{
           
           if($_SESSION["lang"]){
              if($_SESSION["lang"]["iso"] != $settings["lang_site_default"]) header("Location: ".$prefix_dir.$_SESSION["lang"]["iso"]."/");
           }

           $find = findOne("uni_languages","iso = ?", array($settings["lang_site_default"]));

            $_SESSION["lang"]["iso"] = $find->iso;
            $_SESSION["lang"]["name"] = $find->name;
            $_SESSION["lang"]["icon"] = URL.$image_language.$find->image;

            if(file_exists($_SERVER["DOCUMENT_ROOT"]."/lang/".$_SESSION["lang"]["iso"].".php")){
                $languages_content = json_decode( ob_get($_SERVER["DOCUMENT_ROOT"]."/lang/".$_SESSION["lang"]["iso"].".php"), true );
            }else{
                $languages_content = json_decode( ob_get($_SERVER["DOCUMENT_ROOT"]."/lang/".$settings["lang_site_default"].".php"), true );
            }
                  
        }
    }else{
      unset($_SESSION["lang"]);
      if(file_exists($_SERVER["DOCUMENT_ROOT"]."/lang/".$settings["lang_site_default"].".php")){
         $languages_content = json_decode( ob_get($_SERVER["DOCUMENT_ROOT"]."/lang/".$settings["lang_site_default"].".php"), true );
      }        
    }
    
    $REQUEST_URI = implode("/", $URI);

    if(trim($REQUEST_URI, "/") != "ajax/admin" && trim($REQUEST_URI, "/") != "ajax/metrics" && trim($REQUEST_URI, "/") != "ajax/profile" && trim($REQUEST_URI, "/") != "ajax/user" && trim($REQUEST_URI, "/") != "ajax/ads" && trim($REQUEST_URI, "/") != "ajax/geo" && trim($REQUEST_URI, "/") != "ajax/temp_image" && trim($REQUEST_URI, "/") != "ajax/search-city" && trim($REQUEST_URI, "/") != "ajax/shop"){
        $Access->check();
        $Banners->click();
        $Profile->mode();
        dirMedia();              
        @include( "../check_block_project.php" );  
    }

    $routing = array(
         "add_ad" => "route/add_ad.php",
         "add_publication" => "route/add_publication.php",
         "edit_publication" => "route/edit_publication.php",
         "unsubscribe" => "route/unsubscribe.php",
         "subscription" => "route/subscription.php",
         "edit_ad" => "route/edit_ad.php",
         "open-shop" => "route/open_shop.php",
         "feedback" => "route/feedback.php",
         "edit-shop/([^/]*)" => "route/edit_shop.php",
         "search-map" => "route/search_map.php",
         "blog" => "route/blog.php",
         "auth" => "route/auth.php",
         "ulogin" => "systems/ajax/ulogin.php",
         "register" => "route/register.php",
         "board" => "route/board.php",
         "sales" => "route/sales.php",
         "sales/([^/]*)" => "route/sales.php",
         "user/([^/]*)" => "route/user.php",
         "fire-ads" => "route/fire.php",
         "shops" => "route/shops.php",
         "profile" => "route/profile.php",
         "shops/([^/]*)" => "route/shops.php",
         "shop/([^/]*)" => "route/shop.php",
         "shop/([^/]*)/([^/]*)" => "route/shop.php",
         "home" => "route/index.php",
         "ajax/admin" => "systems/ajax/admin.php",
         "ajax/metrics" => "systems/ajax/metrics.php",
         "ajax/geo" => "systems/ajax/geo.php",
         "ajax/ads" => "systems/ajax/ads.php",
         "ajax/profile" => "systems/ajax/profile.php",
         "ajax/user" => "systems/ajax/profile.php",
         "ajax/temp_image" => "systems/ajax/ads.php",
         "ajax/search-city" => "systems/ajax/geo.php",
         "ajax/shop" => "systems/ajax/shop.php",
         "subscription/confirm" => "route/index.php",
         "sitemap.xml" => "sitemap.xml",
    );

    if(!empty($REQUEST_URI) && $REQUEST_URI != "/"){

        foreach($routing as $pattern=>$router){

             if(preg_match("#^".$pattern."$#iu",trim($REQUEST_URI, "/"))){

                 $URI = explode("/", trim($REQUEST_URI, "/"));         
                 $page = $router;

             } 
 
        }

         if($page){

             if(file_exists($page)){  include($page);
             }else{include('files/response/404/404.php');}

         }else{
           
            $explode = explode("/", trim($REQUEST_URI, "/"));

            if($explode[0] == $blog_prefix){

                $explode_2 = explode("/", trim($REQUEST_URI, "/"));
                unset($explode_2[0]);
                $alias = implode("/", $explode_2);
                $get = routeCategoryBlog($alias);

                if($get !== false){
                   $array_data["categories_blog"] = $get;
                   $_SESSION["route_name"] = "blog";
                   include("route/blog.php");
                }else{

                    $array_data["categories_blog"] = routeCategoryBlog($explode[1]);

                    $get = routeArticle($explode);

                    if($get !== false){
                       $array_data["article"] = $get;
                       $_SESSION["route_name"] = "article";
                       include("route/blog-view.php");
                    }else{
                       include('files/response/404/404.php');
                    }

                }

            }else{
                 $content = getContent($REQUEST_URI);
                 if(file_exists($content)){  

                    include($content);               

                 }else{include('files/response/404/404.php');}                
            }



         }

     }else{
        include("route/index.php");
     }
      

?> 