<?php
   /*
   Plugin Name: Encurtador de URLs boo-box
   Plugin URI: http://boo-box.com
   Description: Encurte suas URLs com a boo-box de maneira rápida e fácil! 
   Version: 0.2
   Author: Paulo "GraveHeart" Henrique
   License: GPL2
   */



function curl_post($url, array $post = NULL, array $options = array())
{
    $defaults = array(
        CURLOPT_POST => 1,
        CURLOPT_HEADER => 0,
        CURLOPT_URL => $url,
        CURLOPT_FRESH_CONNECT => 1,
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_FORBID_REUSE => 1,
        CURLOPT_TIMEOUT => 4,
        CURLOPT_POSTFIELDS => http_build_query($post)
    );

    $ch = curl_init();
    curl_setopt_array($ch, ($options + $defaults));
    if( ! $result = curl_exec($ch))
    {
        trigger_error(curl_error($ch));
    }
    curl_close($ch);

    return $result;
} 


//incluindo funções no menu
function cria_menu_pra_bagaca() {
      add_options_page(
                       'Encurtador boo-box',         //Title
                       'Encurtador boo-box',         //Sub-menu title
                       'manage_options', //Security
                       __FILE__,         //File to open
                       'bencurtador_options'  //Function to call
                      );  
}


function bencurtador_options() {
      echo '<div class="wrap"><h2>Encurtador boo-box</h2>';
	if ($_REQUEST['submit']) {
		update_bencurtador_options();
	        }
		print_bencurtador_form();
     echo '</div>';
}


function print_bencurtador_form () {
      $bencurtador_email = get_option('bencurtador_email');   

	echo "<form method=\"post\">";
	echo "<label>Email: <input type=\"text\" name=\"bencurtador_email\" value=\"$bencurtador_email\" /></label><br />";
	echo "<small>Insira o seu email de cadastro na boo-box.</small><br /><br />";
	echo "<br /> <br />  <input type=\"submit\" name=\"submit\" value=\"Submit\" />  </form>";

 }

 function update_bencurtador_options() {
      $updated = false;
      if ($_REQUEST['bencurtador_email']) {
	$url = "http://boo-box.com/profile/login/?boomail=" . $_REQUEST['bencurtador_email'] . "&getlastbid=1";
	$ch = curl_init(); 
	$timeout = 0; 
	curl_setopt ($ch, CURLOPT_URL, $url); 
	curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1); 
	curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, $timeout); 
	$out = curl_exec($ch); 
	echo curl_error($ch);
	curl_close($ch); 
	$json = json_decode($out, true);
	if ($json["userid"]) { 
		update_option("bencurtador_email", $json["email"]);
		update_option("bencurtador_userid", $json["userid"]);
		update_option("bencurtador_bid", $json["lastbid"]);
        	$updated = true;
	}
      }
      if ($updated) {
            echo '<div id="message" class="updated fade">';
            echo '<p>Pronto! Agora você já pode ganhar dinheiro com a boo-box</p>';
            echo '</div>';
       } else {
            echo '<div id="message" class="error fade">';
            echo '<p>Erro! Você não inseriu um email válido ou que esteja cadastrado e nosso sistema.</p>';
            echo '</div>';
       }
  }



function encurto_mermo( $post_id ) {

$url_encurtador = "http://ads.tt/shorten"; // URL do encurtador

	if ( !wp_is_post_revision( $post_id ) ) {

		$post_url = get_permalink( $post_id );
		$bencurtador_email = get_option('bencurtador_email');   
		$bencurtador_userid = get_option('bencurtador_userid');   
		$bencurtador_bid = get_option('bencurtador_bid');   

		$data = array("path" => "$post_url", "publisher_id" => "$bencurtador_userid", "bid" => "$bencurtador_bid");                                                                    
		
		$encurtado = curl_post($url_encurtador, $data);	
		$encurtado = json_decode($encurtado);
		$teste = "$encurtado->path";
		update_post_meta($post_id, 'burl', $teste);
		
	}

}


function mostra_coluna( $columns ) {
	  return array_merge( $columns, 
              array('burl' => __('URL encurtada')) );
}

//echo get_post_meta( $post_id , 'burl' , true ); 


	
function minha_coluna($name) {
    global $post;
    switch ($name) {
        case 'burl':
            $burl = get_post_meta($post->ID, 'burl', true);
            echo $burl;
    }
}


add_filter( 'manage_posts_columns' , 'mostra_coluna');
add_action( 'manage_posts_custom_column',  'minha_coluna');
add_action( 'admin_menu','cria_menu_pra_bagaca');
add_action( 'publish_post', 'encurto_mermo');
?>
