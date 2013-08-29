<?php
   



//Amazon共通の定数
define('AF','kubou-22'); //トラッキング
define('AC','AKIAJP2HCZB6C4IPJORA'); //アクセスキー
define('SEC','0wjD0+q6Qkp5ejUGNKu6kAz3NuEMkBM4IbwIVni8'); //シークレットキー
define('URL','http://ecs.amazonaws.jp/onca/xml'); //リクエスト先のURL



//認証取得用function
function urlencode_rfc3986($str) {
	return str_replace('%7E', '~', rawurlencode($str));
}

function get_key($param){
	ksort($param);
	$canonical_string = '';
	foreach ($param as $k => $v) {
		$canonical_string .= '&'.urlencode_rfc3986($k).'='.urlencode_rfc3986($v);
	}
	$canonical_string = substr($canonical_string, 1);
	$parsed_url = parse_url(URL);
	$string_to_sign = "GET\n{$parsed_url['host']}\n{$parsed_url['path']}\n{$canonical_string}";
	$signature = base64_encode(hash_hmac('sha256', $string_to_sign, SEC, true));
	 
	// 返り値のURLにアクセスするとXMLが取得できます。
	return URL.'?'.$canonical_string.'&Signature='.urlencode_rfc3986($signature);
}

//画像をサーバに保存するfunction
//URLを投げたら指定したディレクトリに保存するだけ。。。。
function dl_image($url, $postid){
	
	$wp_upload_dir = wp_upload_dir();

	
	$tempimg = file_get_contents($url);
	$replacedBaseName = str_replace('%', '_', basename($url));
	file_put_contents($wp_upload_dir[path].'/'.$replacedBaseName,$tempimg);
	unset($tempimg);
	
	
	
	$wp_filetype = wp_check_filetype(basename($tempimg), null );
	
	
// 	$filename = basename($url[0]);
	
// 	$attachment = array(
// 			'guid' => $wp_upload_dir['url'] . '/' . $filename,
// 			'post_mime_type' => '',
// 			'post_title' => preg_replace('/\.[^.]+$/', '', $filename),
// 			'post_content' => '',
// 			'post_status' => 'inherit',
// 			'post_parent' => $postid,
// 			'post_type' => 'attachment'
			
// 	);
// 	$attach_id = wp_insert_attachment( $attachment, $filename, $postid);

// 	require_once(ABSPATH . 'wp-admin/includes/image.php');
// 	$attach_data = wp_generate_attachment_metadata( $attach_id, $filename );
// 	wp_update_attachment_metadata( $attach_id, $attach_data );
	
	
	
// 	return WP_CONTENT_DIR.'/uploads/'.basename($url);
	return $wp_upload_dir[url].'/'.$replacedBaseName;
}



//ここから実際の取得処理------------------------------------------
//取得処理はwp_cronで動かしますのでadd_actionは不要です。
function get_amazon(){
	
	print_r('hogehoge');exit;
	
	$ct = array(
			'1' => '515206' //WP上のカテゴリID => Amazon上のカテゴリNo
	);
	 
	//ページカウンター
	//1ページずつ取っていきます。どこまで取得できたかをget_optionで保持している
	if(get_option('count')){
		$i = get_option('count');
	} else {
		$i = 1;
		add_option('count',1);
	}
	
	// @todo 開発用：常に１ページ目を取得するように。
// 	$i = 1;

	foreach($ct as $key=>$val){
		//Amazonで必要なパラメーター類
		$param = array(
				'Service' => 'AWSECommerceService',
				'AWSAccessKeyId' => AC,
				'Operation' => 'ItemSearch',
				'AssociateTag' => AF,
				'ResponseGroup' => 'ItemAttributes,Images,Reviews',
				'SearchIndex' => 'Books',
				'BrowseNode' => $val,
				'Timestamp' => gmdate('Y-m-d\TH:i:s\Z'),
				'ItemPage' => $i
		);
		 
		//先に定義した認証パラメータ付きURLを生成
		$recurl = get_key($param);
		 
		//ページカウントを進める
		//XML取得の途中で落ちてもいいようにforeach入る前にしてますがforeach後のほうが自然かも
		update_option('count',$i + 1);

		//XML取得
		$xml = simplexml_load_file($recurl);
		
		foreach($xml->Items->Item as $key2=>$val2){
			 
			$actarray = array();
			foreach($val2->ItemAttributes->Actor as $act){
				$actarray[] = (String)$act;
			}
			 
			//ここで記事として投稿処理を行う
			//ページスラッグがEANコード（ISBN的なものだと思われる）、タグをキャストとして投稿していますが適宜カスタムフィールドや本文などにしてもいい。
			$postid = wp_insert_post(array(
					'post_status' => 'publish',
					'post_category' => array($key),
					'post_title' => $val2->ItemAttributes->Title,
					'post_name' => $val2->ItemAttributes->EAN,
					'tags_input' => $actarray
			));
			 
			
			//重複チェック
			//同じ商品を取ってしまった場合、EANが同じになるので末尾に「-2」などと付加される。その「-」があれば一回投稿した記事を削除する。
			//なお、無条件で「-」入りだと削除しているのでEAN以外をpost_nameにしていると間違って削除されるおそれがあります。
			$temppost = get_page($postid);
			if(strpos($temppost->post_name, '-') === true){
				wp_delete_post($postid);
// 				print_r($postid);exit;
			} else {
				 
				//カスタムフィールドの付加
				//商品URLや画像URLに加えて最終更新日としてtime()を追加している。理由は後述。
				if($postid){
					add_post_meta($postid,'DetailPageURL',(String)$val2->DetailPageURL,true);
					add_post_meta($postid,'SmallImage',(Array)$val2->SmallImage,true);
					add_post_meta($postid,'MediumImage',(Array)$val2->MediumImage,true);
					add_post_meta($postid,'LargeImage',(Array)$val2->LargeImage,true);
					add_post_meta($postid,'IFrameURL',(String)$val2->CustomerReviews->IFrameURL,true);
					add_post_meta($postid,'LastUpdate',(String)time(),true);
					 

					//画像をローカルに保存する
// 					$smalimg = $val2->SmallImage->URL;
// 					$smalimg_local = dl_image($smalimg);

// 					$midimg = $val2->MediumImage->URL;
// 					$midimg_local = dl_image($midimg);

					$largeimg = $val2->LargeImage->URL;
					$largeimg_local = dl_image($largeimg, $postid);
					
					$hoge = str_replace('http://wp-test.interworks.jp', '', $largeimg_local);
					
					// postidとlargeimg_local（サーバ上のパス）を使ってpost内容のcontentを更新する
					$contentStr = "<a href='".$largeimg_local."'>";
					$contentStr2 = '<img class="alignnone size-medium wp-image-' . $postid . '" alt="" src="' . $largeimg_local . '"/>';
					$contentStr3 = "</a>";
					$contentStr = $contentStr . $contentStr2 . $contentStr3;
					
					$my_post = array();
					$my_post['ID'] = $postid;
					$my_post['post_content'] = $contentStr;
					
					
					// データベースの投稿情報を更新（記事画像）
					wp_update_post( $my_post );
					
					// @todo アイキャッチ画像の投稿
// 					$my_post2 = array();
// 					$my_post2['post_parent'] = $postid;
// 					$my_post2['post_type'] = 'attachment';
// 					$my_post2['guid'] = $largeimg_local;
// 					$my_post2['post_mime_type'] = 'image/jpeg';
					
// // 					wp_update_post( $my_post2 );
// 					wp_insert_attachment($my_post2);
					
					$filename = $hoge;
					
					$wp_filetype = wp_check_filetype(basename($filename), null );
					$wp_upload_dir = wp_upload_dir();
					$attachment = array(
							'guid' => $largeimg_local,
							'post_mime_type' => $wp_filetype['type'],
							'post_title' => preg_replace('/\.[^.]+$/', '', basename($filename)),
							'post_content' => '',
							'post_status' => 'inherit'
					);
					$attach_id = wp_insert_attachment( $attachment, $filename, $postid );
					// you must first include the image.php file
					// for the function wp_generate_attachment_metadata() to work
					require_once(ABSPATH . 'wp-admin/includes/image.php');
					$attach_data = wp_generate_attachment_metadata( $attach_id, $filename );
					wp_update_attachment_metadata( $attach_id, $attach_data );
										

				}
			}
		}//end foreach xml
	}//end foreach
}//Amazonからの取得処理ここまで



//Cronの設定---------------------------------------------------------
//デフォルトのdailyやweeklyだけだと使い勝手が悪いのでいくつか足しておく
//このcron_schedulesとwp_schedule_eventはテンプレ的にこのままでいいでしょう。
add_filter('cron_schedules','cron_add');
function cron_add($schedules){
	$schedules['90min'] = array(
			'interval' => 5400,
			'display' => __( '90min' )
	);
	$schedules['100min'] = array(
			'interval' => 6000,
			'display' => __( '100min' )
	);
	$schedules['120min'] = array(
			'interval' => 7200,
			'display' => __( '120min' )
	);
	$schedules['1min'] = array(
			'interval' => 60,
			'display' => __( '1min' )
	);	
	return $schedules;
}

//Cronに上記のget_amazon()を登録する
//90分単位で動作します。
// add_action('cron_for_amazon', 'get_amazon');
// function my_activation() {
// 	if ( !wp_next_scheduled( 'cron_for_amazon' ) ) {
// 		wp_schedule_event(time(), '1min', 'cron_for_amazon');
// 	}
// }
// add_action('wp', 'my_activation');




//ここからは商品データアップデート処理------------------------------------------
//Amazonは規約で24時間以上のキャッシュ保存が認められません。そこで前述のカスタムフィールドに加えている「LastUpdate」を見て、24時間が経過している場合は更新処理を行います。

function update_amazon(){
	if(is_single()){
		global $post;
		 
		//LastUpdateと現在時刻の差が「60秒*60分*24時間」以上なら処理する
		if($post->LastUpdate < (time() - 86400 )){
			$param = array(
					'Service' => 'AWSECommerceService',
					'AWSAccessKeyId' => AC,
					'Operation' => 'ItemLookup',
					'AssociateTag' => AF,
					'ResponseGroup' => 'ItemAttributes,Images,Reviews',
					'IdType' => 'EAN',
					'SearchIndex' => 'DVD',
					'ItemId' => $post->post_name, //EANさえあれば商品は引っ張れる
					'Timestamp' => gmdate('Y-m-d\TH:i:s\Z'),
			);
			$recurl = get_key($param);
			$xml = simplexml_load_file($recurl);
			if($xml->Items->Item){
				foreach($xml->Items->Item as $val2){
					update_post_meta($postid,'DetailPageURL',(String)$val2->DetailPageURL);
					update_post_meta($postid,'SmallImage',(Array)$val2->SmallImage);
					update_post_meta($postid,'MediumImage',(Array)$val2->MediumImage);
					update_post_meta($postid,'LargeImage',(Array)$val2->LargeImage);
					update_post_meta($postid,'IFrameURL',(String)$val2->CustomerReviews->IFrameURL);
					update_post_meta($postid,'LastUpdate',(String)time());
					 
					//画像の差し替え
					//引数無しfile_put_contentsなので上書きされます
					$smalimg = $val2->SmallImage->URL;
					$smalimg_local = dl_image($smalimg);

					$midimg = $val2->MediumImage->URL;
					$midimg_local = dl_image($midimg);

					$largeimg = $val2->LargeImage->URL;
					$largeimg_local = dl_image($largeimg);
				}
			}

		}
	}
}//func end

//shutdownにフックすることで見る人にも優しい（はず）
add_action('shutdown','update_amazon');



function catch_that_image() {
	global $post, $posts;
	$first_img = '';
	ob_start();
	ob_end_clean();
	$output = preg_match_all('/<img.+src=[\'"]([^\'"]+)[\'"].*>/i', $post->post_content, $matches);
	$first_img = $matches [1] [0];

	if(empty($first_img)){ //Defines a default image
		$first_img = "/images/default.jpg";
	}
	return $first_img;
}












	// Add RSS links to <head> section
	add_theme_support('automatic-feed-links') ;
	
	// Load jQuery
	if ( !function_exists('core_mods') ) {
		function core_mods() {
			if ( !is_admin() ) {
				wp_deregister_script('jquery');
				wp_register_script('jquery', ("http://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js"));
				wp_register_script('jquery.masonry', (get_template_directory_uri()."/js/jquery.masonry.min.js"),'jquery',false,true);
				wp_register_script('gridly.functions', (get_template_directory_uri()."/js/functions.js"),'jquery.masonry',false,true);
				
				wp_enqueue_script('jquery');
				wp_enqueue_script('jquery.masonry');
				wp_enqueue_script('gridly.functions');
			}
		}
		core_mods();
	}
	
	// content width
	if ( !isset( $content_width ))  {
		$content_width = 710; 
	}


	// Clean up the <head>
	function removeHeadLinks() {
    	remove_action('wp_head', 'rsd_link');
    	remove_action('wp_head', 'wlwmanifest_link');
    }
    add_action('init', 'removeHeadLinks');
    remove_action('wp_head', 'wp_generator');
    
	// Gridly post thumbnails
	add_theme_support( 'post-thumbnails' );
// 		add_image_size('summary-image', 310, 9999);
// 		add_image_size('detail-image', 770, 9999);
		
	add_image_size('summary-image', 310, 310, true);
	add_image_size('detail-image', 770, 770, true);
	add_image_size('catch-image', 50, 50, true);

		
	
	
    // menu 
	add_action( 'init', 'register_gridly_menu' );

	function register_gridly_menu() {
		register_nav_menu( 'main_nav', __( 'Main Menu' ) );
	} 

     //setup footer widget area
	if (function_exists('register_sidebar')) {
    	register_sidebar(array(
    		'name' => 'Footer',
    		'id'   => 'gridly_footer',
    		'description'   => 'Footer Widget Area',
    		'before_widget' => '<div id="%1$s" class="widget %2$s"><div class="widget-copy">',
    		'after_widget'  => '</div></div>',
    		'before_title'  => '<h3>',
    		'after_title'   => '</h3>'
    	));
	}


	// hide blank excerpts 
	function custom_excerpt_length( $length ) {
	return 0;
	}
	add_filter( 'excerpt_length', 'custom_excerpt_length', 999 );
	
	function new_excerpt_more($more) {
       global $post;
	return '';
	}
	add_filter('excerpt_more', 'new_excerpt_more');



	// Gridly theme options 
	include 'options/admin-menu.php';

?>