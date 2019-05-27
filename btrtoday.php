<?php

require_once("_load.php");

class BTRtoday {
	
	public $section;
	public $page;
	public $landing_pages;
 
    public function __construct(){
      
       $this->landing_pages = new BTRtodayLandingPages();
    
		add_action( 'init', array( $this, "init" ) );
        add_action( 'admin_init', array($this,"admin_init"));
        
        add_action( 'admin_menu', array($this, 'admin_menu'));
        
    }
    
    public function __destruct(){}
    
    
    public function init(){
        
		#we don't want them.
        $this->disable_unwanted_wp();
        
		#yes, we need thumbnails
        add_theme_support( 'post-thumbnails' );

		#Register Post Types
        $this->register_post_types();
		
		#Taxonomies
        $this->register_taxonomies();
		
		#Rewrites
        $this->rewrites();
        
        $this->capabilities();

        #modify links - what's this?
        add_filter( 'post_type_link', array($this,'modify_links'), 10, 2 );
        add_action( 'wp_enqueue_scripts', array( $this, 'loadStyles' ));
        
        #remove xmlrpc / harden site.
        add_filter( 'xmlrpc_enabled' , '__return_false ' );
       
    
    }
   
    public function user_posts(){
        set_time_limit(0);
        global $wpdb;
        $users = get_users();
        echo "Total:  ".count($users)." users<br>";
        
        foreach($users as $i=>$user){
            echo "<hr>";
            echo $user->display_name."<br>";
            $years = getUserArchiveYears($user->ID);
            foreach($years as $year){
                
                echo $year.":";
                $posts = getUserPostsByYear($user->ID, $year,true);
                echo count($posts)." posts<br>";
                
                foreach($posts as $post){
                    
                    $sql = "SELECT * from user_post WHERE user_id='{$user->ID}' AND post_id='{$post->ID}'";
                    $results = $wpdb->get_results($sql);
                    if(count($results)>0){
                        continue;
                    }
                    $sql = "INSERT INTO user_post (user_id, post_id, post_type, post_date) VALUES ('{$user->ID}','{$post->ID}','{$post->post_type}','{$post->post_date}')";
                    echo $sql."<br>";
                    $wpdb->query($sql);
                }
            }
            
        }
        return;
                             
    }
    
    
    public function user_series(){
        set_time_limit(0);
        global $wpdb;
        
        if(0){
            $podcasts = get_podcast_series();
            foreach($podcasts as $pod){
                echo "series name:".$pod->name."<br>".
                "host name:".$pod->host->display_name."<br>";
                $sql = "INSERT INTO user_series (user_id, series_id,series_type) VALUES ('{$pod->host->ID}','{$pod->term_id}','listen')";
                echo $sql."<br>";
                $wpdb->query($sql);
                if($post->contributors){
                    foreach($post->contributors as $contrib){
                        echo "contributors:" . $contrib->display_name."<br>";
                    }
                    $sql = "INSERT INTO user_series (user_id, series_id, series_type) VALUES ('{$contrib->ID}','{$pod->term_id}','listen')";
                    echo $sql."<br>";
                    $wpdb->query($sql);
                }
                
               echo "<hr>";
            }
        }
        
        if(0){
            $video_series = get_video_series();
            foreach($video_series as $series){
                
                echo "series name:".$series->name."<br>".
                "host name:".$series->executive_producer['display_name']."<br>";
                $sql = "INSERT INTO user_series (user_id, series_id,series_type) VALUES ('{$series->executive_producer['ID']}','{$series->term_id}','tv')";
                echo $sql."<br>";
                $wpdb->query($sql);
                if($series->producers){
                    
                    foreach($series->producers as $contrib){
                        
                        echo "producer:" . $contrib['producer']['display_name']."<br>";
                        $sql = "INSERT INTO user_series (user_id, series_id, series_type) VALUES ('{$contrib['producer']['ID']}','{$series->term_id}','tv')";
                        echo $sql."<br>";
                        $wpdb->query($sql);
                    }
                    
                    
                }
                
               echo "<hr>";
            }
        }
        
        #editorial sections are a no-go.
        
    if(0){
        $section = get_editorial_section();
            foreach($section as $series){
                
                echo "series name:".$series->name."<br>".
                "host name:".$series->executive_producer['display_name']."<br>";
                $sql = "INSERT INTO user_series (user_id, series_id,series_type) VALUES ('{$series->executive_producer['ID']}','{$series->term_id}','tv')";
                echo $sql."<br>";
                //$wpdb->query($sql);
                if($series->producers){
                    
                    foreach($series->producers as $contrib){
                        
                        echo "producer:" . $contrib['producer']['display_name']."<br>";
                        $sql = "INSERT INTO user_series (user_id, series_id, series_type) VALUES ('{$contrib['producer']['ID']}','{$series->term_id}','tv')";
                        echo $sql."<br>";
                  //      $wpdb->query($sql);
                    }
                    
                    
                }
                
               echo "<hr>";
            }
        }
            
    }
    public function update_post_authors(){
        set_time_limit(0);
        global $wpdb;
        
        $sql = "SELECT * from wp_posts WHERE post_status='publish' AND post_type IN('tv','read','listen') ORDER BY post_date DESC";
        $results = $wpdb->get_results($sql);
        foreach($results as $result){
            echo $result->post_author." ".$result->post_date." ".$result->post_title."<br>";
        }
		//$sql = "SELECT * from wp_posts p join wp_postmeta pm ON p.ID = pm.post_ID JOIN wp_users u ON p.post_author = u.ID WHERE u.user_nicename='djmaia'";
		return;
        foreach($results as $result){
			
            $post = postify($result);
            echo $post->post_date."<br>";
            switch($post->post_type){
                case "read":
                    $author_id = $post->lead_writers[0]['ID'];
                    break;
                case "listen":
                    $author_id = $post->hosts[0]->ID;
                    
                    break;
                case "tv":
					$author_id = 168;
                    break;
            }
            
            
            $sql = "UPDATE wp_posts SET post_author='$author_id' WHERE ID='{$post->ID}'";
            echo $sql."<br>";
            $wpdb->query($sql);
            
        }
        
        $begin = array_shift($results);
            $end = array_pop($results);
            echo "beginning: ".$begin->post_date."<br>";
            echo "end: ".$end->post_date."<br>";
        
    }
    
    
    public function admin_init(){
        // not allowed to edit the themes from admin
        define('DISALLOW_FILE_EDIT', TRUE);
        return;
    }
    
    public function admin_menu(){
        
        #update fix
        #add_submenu_page ( "tools.php", "Update Post Authors", "Update Post Authors", "manage_options", "update_post_authors", array($this,'update_post_authors') );
        #initiate meta tables
        #add_submenu_page ( "tools.php", "Create User / Series Meta Data", "Create User / Series Meta Data", "manage_options", "user-series", array($this,'user_series'));
        
        #initiate meta tables
        #add_submenu_page ( "tools.php", "Create User / Post Meta Data", "Create User / Post Meta Data", "manage_options", "user-posts", array($this,'user_posts'));
        
        
        #get rid of comments admin, we don't need it.
        remove_menu_page("edit-comments.php");
        
        if(!current_user_can("edit_landing_pages")){
            remove_menu_page("edit.php?post_type=landing");
        }
        
        if(!current_user_can("manage_options")){ 
            remove_menu_page("tools.php");
        }
        
        if(!current_user_can("manage_legacy_posts")){
            remove_menu_page("edit.php");
        }
        
        if(!current_user_can("edit_pages")){
            remove_menu_page("edit.php?post_type=page");
        }
        
        // remove sponsor pages from non admins
        if(!current_user_can("edit_sponsors")){
            remove_submenu_page("edit.php?post_type=read","edit-tags.php?taxonomy=sponsor&amp;post_type=read");
            remove_submenu_page("edit.php?post_type=listen","edit-tags.php?taxonomy=sponsor&amp;post_type=listen");
            remove_submenu_page("edit.php?post_type=tv","edit-tags.php?taxonomy=sponsor&amp;post_type=tv");
        }
        
        if(!current_user_can("edit_series")){
            remove_submenu_page("edit.php?post_type=read","edit-tags.php?taxonomy=editorial-section&amp;post_type=read");
            remove_submenu_page("edit.php?post_type=listen","edit-tags.php?taxonomy=podcast-series&amp;post_type=listen");
            remove_submenu_page("edit.php?post_type=tv","edit-tags.php?taxonomy=video-series&amp;post_type=tv");
        }
        
        if(!current_user_can("edit_itunes_categories")){
            remove_submenu_page("edit.php?post_type=listen","edit-tags.php?taxonomy=itunes-podcast-category&amp;post_type=listen");
        }
        
        if(!current_user_can("edit_artists")){
            remove_submenu_page("edit.php?post_type=read","edit-tags.php?taxonomy=artist&amp;post_type=read");
            remove_submenu_page("edit.php?post_type=listen","edit-tags.php?taxonomy=artist&amp;post_type=listen");
            remove_submenu_page("edit.php?post_type=tv","edit-tags.php?taxonomy=artist&amp;post_type=tv");
        }
        
        if(!current_user_can("edit_record_labels")){
            remove_submenu_page("edit.php?post_type=read","edit-tags.php?taxonomy=record-label&amp;post_type=read");
            remove_submenu_page("edit.php?post_type=listen","edit-tags.php?taxonomy=record-label&amp;post_type=listen");
            remove_submenu_page("edit.php?post_type=tv","edit-tags.php?taxonomy=record-label&amp;post_type=tv");
        }
        
       
        
    }
    public function loadStyles(){
        
        $style_version = '2.0.5';
		wp_enqueue_style( 'style', get_stylesheet_uri(), array(), $style_version );
		wp_enqueue_style( 'nav-search', get_template_directory_uri()."/nav-search.css", array(), $style_version );
		wp_enqueue_style( 'grid', get_template_directory_uri()."/grid.css", array(), $style_version );
        wp_enqueue_style( 'grid', get_template_directory_uri()."/grid-admin.css", array(), $style_version );
		wp_enqueue_style( 'article', get_template_directory_uri()."/article.css", array(), $style_version );
		wp_enqueue_style( 'podcast', get_template_directory_uri()."/podcast.css", array(), $style_version );
		wp_enqueue_style( 'video', get_template_directory_uri()."/video.css", array(), $style_version );
		wp_enqueue_style( 'recommendations', get_template_directory_uri()."/recommendations.css", array(), $style_version );
		wp_enqueue_style( 'footer', get_template_directory_uri()."/footer.css", array(), $style_version );
		wp_enqueue_style( 'NexaKit', get_template_directory_uri()."/NexaKit/NexaKit.css", array(), $style_version );
		wp_enqueue_style( 'player', get_template_directory_uri()."/player.css", array(), $style_version );
	}
        
    public function register_post_types(){
		
		# hide post_author field from those without permissions
        $supports = current_user_can('edit_post_author')
		?
		array( 'title', 'author', 'editor', 'custom-fields', 'excerpt', 'thumbnail' )
		:
		array( 'title', 'editor', 'custom-fields', 'excerpt', 'thumbnail' );
		
        // videos  
        register_post_type( 'tv',
        array(
          'labels' => array(
            'name' => __( 'Videos' ),
            'singular_name' => __( 'Video' ),
            'all_items'=> __( 'All Videos' ),
            'edit_item'=> __( 'Edit Video' ),
            'add_new_item'=> __('Add New Video Post'),
          ),
          'public' => true,
          'has_archive' => true,
          //'rewrite' => array('slug'=>'tv/%series%'),
          'taxonomies'=>array('post_tag','category'),
          'supports' => array( 'title', 'author', 'editor', 'custom-fields', 'excerpt', 'thumbnail' ),
        )
        );
    
        // articles
        register_post_type( 'read',
            array(
              'labels' => array(
                'name' => __( 'Articles' ),
                'singular_name' => __( 'Article' ),
                'all_items'=> __( 'All Articles' ),
                'edit_item'=> __( 'Edit Article' ),
                'add_new_item'=> __('New Article'),		
                'new_item'=> __('New Article'),		
              ),
              'public' => true,
              'has_archive' => true,
              //'rewrite'=> array('slug'=>'read/%section%/%column%'),
              'taxonomies'=>array('post_tag', 'category'),
              'supports' => array( 'title', 'editor', 'custom-fields', 'excerpt', 'thumbnail','author' ),
            )
          );
          
        // podcasts 
        register_post_type( 'listen',
          array(
            'labels' => array(
              'name' => __( 'Podcasts' ),
              'singular_name' => __( 'Podcast' ),
              'all_items'=> __( 'All Podcasts' ),
              'edit_item'=> __( 'Edit Podcast' ),
              'add_new_item'=> __('Add New Podcast'),
            ),
            'public' => true,
            'has_archive' => true,
            //'rewrite' => array('slug'=>'listen/%series%'),
            'taxonomies'=>array('post_tag','category'),
            'supports' => array( 'title', 'editor', 'custom-fields', 'excerpt', 'thumbnail', 'author' ),
          )
        );

        // landing pages
        register_post_type( 'landing',
          array(
            'labels' => array(
              'name' => __( 'Landing Pages' ),
              'singular_name' => __( 'Landing Page' ),
              'all_items'=> __( 'All Landing Pages' ),
              'edit_item'=> __( 'Edit Landing Page' ),
              'add_new_item'=> __('Add New Landing Page'),
            ),
            'public' => true,
            'has_archive' => false,
            'taxonomies'=>array('post_tag','category'),
            'supports' => array( 'title' ),
          )
        );

    }
    
    // TO DO: Bring all registration into the post classes
    public function register_taxonomies(){
        
        register_taxonomy('video-series',
            'tv',
              array(
                'labels' => array(
                  'name'=>__('Series'),
                  'all_items'=> __( 'All Series' ),
                  'edit_item'=> __( 'Edit Series' ),
                  'add_new_item'=> __('Add New Series'),
                  'separate_items_with_commas' => __(''),
                ),
                'hierarchical'=>true,
                'meta_box_cb' => false,
              )
          );
        
        register_taxonomy('podcast-series',
          'listen',
            array(
              'labels' => array(
                'name'=>__('Series'),
                'all_items'=> __( 'All Series' ),
                'edit_item'=> __( 'Edit Series' ),
                'add_new_item'=> __('Add New Series'),
                'separate_items_with_commas' => __(''),
              ),
              'hierarchical'=>true,
              'has_archive'=>true,
              'meta_box_cb' => false,
              )
          );
          
              /* editorial section taxonomy */
        register_taxonomy('editorial-section',
          'read',
            array(
              'labels' => array(
                'name'=>__('Editorial Sections'),
                'all_items'=> __( 'All Editorial Sections' ),
                'edit_item'=> __( 'Edit Editorial Section' ),
                'add_new_item'=> __('Add New Editorial Section or Column'),
                'update_item'=>__('Update Section'),
                'separate_items_with_commas' => __(''),
              ),
              'hierarchical'=>true,
              'meta_box_cb' => false,
            )
        );
        
          register_taxonomy('sponsor',
            array('listen','tv','read'),
              array(
                'labels' => array(
                  'name'=>__('Sponsors'),
                  'all_items'=> __( 'All Sponsors' ),
                  'edit_item'=> __( 'Edit Sponsor' ),
                  'add_new_item'=> __('Add New Sponsor'),
                  'update_item'=>__('Update Sponsor'),
                  'separate_items_with_commas' => __(''),
                  
                ),
                'hierarchical'=>false,
                'meta_box_cb' => false,
              )
          );
          
          /* genre taxonomy */
          register_taxonomy('genre',
            array("post"),
              array(
                'labels' => array(
                  'name'=>__('Genres'),
                  'all_items'=> __( 'All Genres' ),
                  'edit_item'=> __( 'Edit Genre' ),
                  'add_new_item'=> __('Add New Genre'),
                  'separate_items_with_commas' => __('Separate genres with commas'),
                ),
                'hierarchical'=>true,
              )
          );
          
          /* artist taxonomy */
          register_taxonomy('artist',
            array("listen","tv","read", "attachment"),
              array(
                'labels' => array(
                  'name'=>__('Musical Artists'),
                  'all_items'=> __( 'All Artists' ),
                  'edit_item'=> __( 'Edit Artist' ),
                  'add_new_item'=> __('Add New Artist'),
                  'separate_items_with_commas' => __('Separate artists with commas'),
                ),
                'hierarchical'=>false,
                'has_archive'=>true,
                'rewrite' => array('slug'=>'artist/%artist%'),
                
              )
          );
          
          
          /* record-label taxonomy */
          register_taxonomy('record-label',
            array("listen","tv","read"),
              array(
                'labels' => array(
                  'name'=>__('Record Labels'),
                  'all_items'=> __( 'All Record Labels' ),
                  'edit_item'=> __( 'Edit Record Label' ),
                  'add_new_item'=> __('Add New Record Label'),
                  'separate_items_with_commas' => __('Separate record labels with commas'),
                ),
                'hierarchical'=>false,
                'has_archive'=>true,
                'rewrite' => array('slug'=>'record-label/%label%'),
                
              )
          );
          // attempt to register record-label for artist
        register_taxonomy_for_object_type( "record-label", "artist" );
          
        register_taxonomy('itunes-podcast-category',
            array("listen"),
                array('labels'=>array(
                    'name'=>__('iTunes Podcast Category'),
                    'all_items'=>__('All Podcast Categories'),
                    'edit_item'=>__('Edit Podcast Category'),
                    'add_new_item'=>__('Add New Podcast Category')
                ),
                'hierarchical'=>true,
                'has_archive'=>false,
                "public"=>false
            )
        );
    
   
    }
    
    public function rewrites(){
        $this->rewrite_tags();
        $this->rewrite_rules();
    }
    
    private function rewrite_tags(){
        // series rewrite tag for series (podcasts/videos)
        add_rewrite_tag('%series%', '([^/]+)');
        
        // section and column rewrite tags for articles
        add_rewrite_tag('%section%', '([^/]+)');
        add_rewrite_tag('%column%', '([^/]+)');
    }
    
    private function rewrite_rules(){
        
        // department landing pages
        #add_rewrite_rule("^(listen|tv)/?$", "index.php?post_type=\$matches[1]", "top"); 
        #add_rewrite_rule("^read/?$", "index.php?post_type=landing&name=matches[1]", "top");
        
        $this->podcast_series_rewrites();
        $this->video_series_rewrites();
        $this->editorial_section_rewrites();
        $this->user_rewrites();
        $this->artist_rewrites();
    }
    
    private function capabilities(){
        
        #administrator
        $role = get_role("administrator");
        $role->add_cap("edit_sponsors");
        $role->add_cap("edit_series");
        $role->add_cap("edit_itunes_categories");
        $role->add_cap("edit_artists");
        $role->add_cap("edit_record_labels");
        $role->add_cap("edit_landing_pages");
        $role->add_cap("manage_legacy_content");
		$role->add_cap("edit_post_author");
        $role->add_cap("view_series_analytics");
        #tbd - redirect to warning page if url is pasted in without capabilities
        
        
        #editor
        $role = get_role("editor");
		$role->add_cap("edit_post_author");
        $role->remove_cap("manage_categories");
        $role->remove_cap("edit_pages");
        $role->add_cap("view_series_analytics");
        
        #author
        $role = get_role("author");
        $role->add_cap("unfiltered_html");
        $role->add_cap("view_series_analytics");
        
        #contributor
        $role = get_role("contributor");
        $role->add_cap("view_series_analytics");
        
    }
    
    private function podcast_series_rewrites(){
        
        /*  --- listen section rewrite rules ---*/
        // add series landing page rewrite rules
        // taxonomy query for podcast-series
        $allseries = get_terms( 'podcast-series', array(
            'hide_empty' => false,
        ) );
    
        $series_slugs = array();
        $series_categories = array();
        
        foreach($allseries as $series){
            
          $series_slugs[] = $series->slug;
          $categories = get_field("podcast_category","podcast-series_".$series->term_id);
          
          if(!empty($categories)){
            
            foreach($categories as $category){
                
              if( $cat = get_term($category) ){
              
                if(array_search($cat->slug, $series_categories)===false){
                  $series_categories[] = $cat->slug;
                }
                
              }
            }
          }
          else{
            
            // why would a series have no categories?
            
          }
          
        }
        
        $series_rgx = implode("|",$series_slugs);
        $series_categories_rgx = implode("|", $series_categories);
        
        
        // series list category filter;
        add_rewrite_rule("^listen/($series_categories_rgx)/?$", "index.php?pagename=podcast-list&category_name=\$matches[1]", "top");
            
        // individual series landing pages
        add_rewrite_rule("^listen/($series_rgx)/?$", "index.php?podcast-series=\$matches[1]", "top");
    
        /*NOTE*
         *on 11/6/2017 this piece broke somehow, and it was working before that.
         *it was matching "feed" in the podcast regex, and looking for a podcast named "feed"
         *it would 404.
         *putting the itunes rss match earlier in the block fixed it.
         *it should be done with a proper regex though.
         */
        // use custom rss formatting
        add_feed("itunes", "getRss");
        // podcast series feed
        add_rewrite_rule("^listen/($series_rgx)/feed/?$", "index.php?feed=itunes&podcast-series=\$matches[1]", "top");
        
        // individual series pages
        add_rewrite_rule("^listen/($series_rgx)/?$", "index.php?podcast-series=\$matches[1]&department=listen", "top");
        
        # archives 
        add_rewrite_rule("^listen/($series_rgx)/(2[0-9][0-9][0-9])/?$", "index.php?pagename=archives&term=\$matches[1]&department=listen&archive-type=podcast-series&archive-year=\$matches[2]", "top");
        add_rewrite_rule("^listen/($series_rgx)/(2[0-9][0-9][0-9])/([A-Za-z][a-z][a-z])/?$", "index.php?pagename=archives&term=\$matches[1]&department=listen&archive-type=podcast-series&archive-year=\$matches[2]&archive-month=\$matches[3]", "top");
	
        add_rewrite_rule("^embed/listen/($series_rgx)/([^/]+)/?$", "index.php?pagename=player&em_postname=\$matches[2]", "top");
        
        // podcast page
        add_rewrite_rule("^listen/($series_rgx)/([^/]+)/?$", "index.php?podcast-series=\$matches[1]&listen=\$matches[2]", "top");
       
        
        // series list page
        add_rewrite_rule("^listen/all/?$", "index.php?pagename=podcast-list", "top");
        // series archives page
        add_rewrite_rule("^listen/archives/?$", "index.php?pagename=podcast-archives", "top");
        
    }
    
    public function video_series_rewrites(){
        /* ----- video section rewrite rules ----- */
    
        // taxonomy query for podcast-series
        $allseries = get_terms( 'video-series', array(
            'hide_empty' => false,
        ) );
        
        $series_slugs = array();
        
        foreach($allseries as $series){
          $series_slugs[] = $series->slug;
        }
        $series_rgx = implode("|",$series_slugs);
        add_rewrite_rule("^tv/($series_rgx)/?$", "index.php?video-series=\$matches[1]", "top");
        add_rewrite_rule("^tv/($series_rgx)/(2[0-9][0-9][0-9])/?$", "index.php?pagename=archives&term=\$matches[1]&department=tv&archive-type=video-series&archive-year=\$matches[2]", "top");
        add_rewrite_rule("^tv/($series_rgx)/(2[0-9][0-9][0-9])/([A-Za-z][a-z][a-z])/?$", "index.php?pagename=archives&term=\$matches[1]&department=tv&archive-type=video-series&archive-year=\$matches[2]&archive-month=\$matches[3]", "top");
	
        add_rewrite_rule("^tv/($series_rgx)/([^/]+)/?$", "index.php?video-series=\$matches[1]&tv=\$matches[2]", "top");
    }
    
    public function editorial_section_rewrites(){
        $allsections = get_terms( 'editorial-section', array(
            'hide_empty' => false,
            'parent'=>0
        ) );
        
        $section_slugs = array();
        $childless = array();
        
        $parentsections = array();
        foreach($allsections as $section){
            $childsections = get_term_children($section->term_id,'editorial-section');
            if(count($childsections)){
                $childslugs = array();
                foreach($childsections as $child){
                    $childslugs[] = get_term($child)->slug;
                }
                $parentsections[$section->slug] = implode("|",$childslugs);
                
            }
            else{
                $childless[] = $section->slug;
            }
            $section_slugs[] = $section->slug;
        
        }
        
        $childless = implode("|",$childless);
        $section_rgx = implode("|",$section_slugs);
        
        #section
        add_rewrite_rule("^read/($section_rgx)/?$", "index.php?post_type=read&editorial-section=\$matches[1]", "top");
        
        #section archives
        add_rewrite_rule("^read/($section_rgx)/(2[0-9][0-9][0-9])/?$", "index.php?pagename=archives&term=\$matches[1]&department=read&archive-type=editorial-section&archive-year=\$matches[2]", "top");
        add_rewrite_rule("^read/($section_rgx)/(2[0-9][0-9][0-9])/([A-Za-z][a-z][a-z])/?$", "index.php?pagename=archives&term=\$matches[1]&department=read&archive-type=editorial-section&archive-year=\$matches[2]&archive-month=\$matches[3]", "top");
        
        #article
        add_rewrite_rule("^read/($section_rgx)/([^/]+)/?$", "index.php?editorial-section=\$matches[1]&read=\$matches[2]", "top");
        
        // music subsection rules
        foreach($parentsections as $parent=>$child_rgx){
            add_rewrite_rule("^read/$parent/($child_rgx)/([^/]+)/?$", "index.php?editorial-section=\$matches[1]&read=\$matches[2]", "top");
        }
    }
    
    public function user_rewrites(){
        
        $users = $this->get_podcast_hosts();
        $user_slugs = array();
        foreach($users as $user){
            $user_slugs[] = $user->user_nicename;
        }
        $user_rgx = implode("|",$user_slugs);
        add_rewrite_rule("^($user_rgx)/?$", "index.php?pagename=user-profile&author=\$matches[1]", "top");
        add_rewrite_rule("^($user_rgx)/feed/?$", "index.php?feed=itunes&author=\$matches[1]", "top");
        
    }

    public function artist_rewrites(){
        
        #archives
        add_rewrite_rule("^artist/([^/]+)/(2[0-9][0-9][0-9])/?$", "index.php?pagename=archives&term=\$matches[1]&department=general&archive-type=artist&archive-year=\$matches[2]", "top");
        add_rewrite_rule("^artist/([^/]+)/(2[0-9][0-9][0-9])/([A-Za-z][a-z][a-z])/?$", "index.php?pagename=archives&term=\$matches[1]&department=general&archive-type=artist&archive-year=\$matches[2]&archive-month=\$matches[3]", "top");
    
    }
    
    public function modify_links( $link, $post ) {
    
      switch($post->post_type){
        case "listen":
          
          $series = get_term(get_field('podcast_series',$post->ID),'podcast-series');
          
          if ( $series  ) {
              $inject_pos = strrpos(trim($link,"/"),"/");
              $link = substr($link,0,$inject_pos+1).$series->slug.substr($link,$inject_pos);
          } 
          break;
        case "tv":
          if ( $series = get_term( get_field('video_series',$post->ID), 'video-series' ) ) {
            $inject_pos = strrpos(trim($link,"/"),"/");
            $link = substr($link,0,$inject_pos+1).$series->slug.substr($link,$inject_pos);
          }
    
          break;
        case "read":
            
            $section = get_term( get_field('section_name',$post->ID),'editorial-section');
           
            if( !is_wp_error($section)){
              
                if ($section->parent > 0){
                    $parent = get_term($section->parent);
                    $inject_pos = strrpos(trim($link,"/"),"/");
                    $link = substr($link,0,$inject_pos+1).$parent->slug."/".$section->slug.substr($link,$inject_pos);
                }
                else{
                    $inject_pos = strrpos(trim($link,"/"),"/");
                    $link = substr($link,0,$inject_pos+1).$section->slug.substr($link,$inject_pos);
                }
            }
            
            break;
            default:
                
            break;
      }

      return $link;
    }
    
    // remove unessential default WP functionality
    public function disable_unwanted_wp() {

        // all actions related to emojis
        remove_action( 'admin_print_styles', 'print_emoji_styles' );
        remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
        remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
        remove_action( 'wp_print_styles', 'print_emoji_styles' );
        remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );
        remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
        remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
        
        // Disables Kses only for textarea saves
        foreach (array('pre_term_description', 'pre_link_description', 'pre_link_notes', 'pre_user_description') as $filter) {
            remove_filter($filter, 'wp_filter_kses');
        }
        
        // Disables Kses only for textarea admin displays
        foreach (array('term_description', 'link_description', 'link_notes', 'user_description') as $filter) {
            remove_filter($filter, 'wp_kses_data');
        }

      
    }
    
    // note:  why did I have to remove these?
    public function deregister_scripts(){
        wp_deregister_script( 'wp-embed' );
        wp_deregister_script( 'wp-embed.min' );
    }
    
    
    private function fixes(){
        
        // needed to force time zone, despite php ini being set.  y?  not sure.
        //date_default_timezone_set('America/New_York');
        
    }
    
    private function get_podcast_hosts(){
        global $wpdb;
        $sql = "SELECT DISTINCT(user_id) FROM user_series";
        $results = $wpdb->get_results($sql);
        $users = [];
        foreach($results as $result){
            $user = get_user_by('ID',$result->user_id);
            $users[] = $user;
            
        }
        
        return $users;
    }
    
    
    public function is_btrtoday_post($post){
        return in_array($post->post_type,array('listen','read','tv'));
    }
 }
 
 
 $btrtoday = new BTRtoday();