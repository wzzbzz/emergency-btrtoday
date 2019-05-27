<?php
#### WE NEED AN IMAGE URL FIELD ADDED.
#### COMPARE AND CONTRAST WITH MASTER

require_once "page.php";

class BTRtodayLandingPages { // extends SystemComponent{
    #code
    private $page;
	private $page_types;
	private $cell_types;
	private $departments;
	
	private function set_page_types(){
		$this->page_types = array(
			"homepage"=>"Home Page",
			"department_landing"=>"Department Landing",
			"category_landing"=>"Category Landing",
			"general"=>"General Page"
		);
	}
	
	private function set_departments(){
		$this->departments = array(
			"general"=>"General",
			"tv"=>"Watch",
			"listen"=>"Listen",
			"read"=>"Read"
		);
	}

	private function set_cell_types(){
	/* here's where we set our cell types */
	
		$this->cell_types = array(
			"1x1" => "1x1",
			"2x2" => "2x2",
			"3x1" => "3x1",
			"3x3" => "3x3",
			"3x6" => "3x6",
            "homepage-feature" => "Homepage Feature",
			"special-series" => "Series",
			"special-topartists" => "Top Artists",
		);
	
	}
	public function __construct($args = null){
		// check for conditions
		// Wordpress present, etc.
		
		if(!$this->check_requirements($args)){
			
			return false;
		
		}
		
		$this->set_page_types();
		$this->set_departments();
		$this->set_cell_types();
		
		$this->actions();
		$this->filters();
				   
	}
	public function __destruct(){}
	
	private function check_requirements(){
		return true;
	}
	
	private function actions(){
		add_action('init',array($this,'init'));
		add_action('admin_init',array($this,'admin_init'));
	}
	
	private function filters(){
		
	}
	
	#wordpress init hook
	public function init(){
        // here's where we'll do the rewrites
        // thus making this the controller class.
        // need 2 view classes
        // admin view
        // public view
        
        $pages = $this->get_landing_pages();
        
        foreach($pages as $page){
            $path = trim($page->get_path(),"/");
            
            add_rewrite_rule("^({$path})/?$", "index.php?post_type=landing&name=\$matches[1]", "top");
        }
        
	}
	
	#wordpress admin_init hook
	public function admin_init(){
		
		add_action( 'add_meta_boxes', array($this,'add_meta_boxes'), 10, 2 );
        add_action ( 'admin_enqueue_scripts', array($this,'admin_enqueue_scripts'));
        add_action ( 'save_post', array($this,'save_post'),10,3);
		
	}
	
	public function add_meta_boxes(){
		
		add_meta_box( 
			 'landing-page-details-meta-box',
			 __( 'Landing Page Info' ),
			 array($this,'render_admin'),
			 'landing',
			 'normal',
			 'default'
		 );
	 
	}
	
    public function admin_enqueue_scripts(){
        $post_type = get_post_type();
        if($post_type!="landing"){
            return;
        }
        wp_enqueue_script('landing-page-admin-grid', get_template_directory_uri() . '/js/landing-page-admin-grid.js','','',true);
        wp_enqueue_style( 'btr-modules-widget-style', get_template_directory_uri() . '/grid-admin.css' );
        
        function terms_array($terms){
            
            $data = array();
            foreach($terms as $term){
                $obj = new stdClass();
                $obj->id = $term->term_id;
                $obj->slug = $term->slug;
                $obj->name = $term->name;
                $obj->parent = $term->parent;
                $data[]=$obj;
            }
            
            return $data;
        }
        
        $terms = get_terms("category");
        $categories['categories']=terms_array(sort_terms_hierarchically($terms));
        
        $terms = get_terms("podcast-series");
        $categories['series']['listen']=terms_array($terms);
        
        $terms = get_terms("editorial-section");
        $categories['series']['read'] = terms_array($terms);
        
        $terms = get_terms('video-series');
        $categories['series']['tv'] = terms_array($terms);
        
        wp_localize_script( 'landing-page-admin-grid', 'categories', $categories);
            
    }
    
	public function render_admin($post) {
		
		$this->landing_page = new BTRtoday_Landing_Page($post);
		?>
		<p>
			<?php
				$this->admin_page_type();
				$this->admin_masthead_title();
                $this->admin_page_title();
                $this->admin_path();
				$this->admin_credits();
				$this->admin_meta_description();
                $this->admin_image_url();
				$this->admin_department();
				$this->admin_designation();
				$this->admin_grid();
			?>
		</p>
		<?php 
	}
	
	private function admin_page_type(){
		

		$page_type = empty( $this->landing_page->page_type ) ? "" : $this->landing_page->page_type;

		?>
		<label for="landing-page-type"><?php esc_attr_e('Page Type:', 'text_domain'); ?></label>
		<?= $this->makeSelect('landing-page-type', 'landing-page-type', $this->page_types, $page_type, array("widefat", "page_type")); ?>		
		<?php if ($page_type == 'category_landing'): ?>
			<?= $this->makeCategorySelect("general", "category", $this->landing_page->page_category, 'page-category', 'page-category', array("widefat", "page-category")); ?>
		<?php endif; ?>
		<?php
	}
	
	private function admin_masthead_title() {
		
		$title = !empty($this->landing_page->masthead_title) ? $this->landing_page->masthead_title : esc_html__('', 'text_domain');
		
		?>
		<label for='page-title'> <?php esc_attr_e('Masthead Title:', 'text_domain'); ?></label> 
		<input class="widefat title" id="masthead_title" name="masthead_title" type="text" value="<?= esc_attr($title) ?>">
		<?php
		
	}

	
	private function admin_page_title() {
		
		$title = !empty($this->landing_page->page_title) ? $this->landing_page->page_title : esc_html__('', 'text_domain');
		
		?>
		<label for='page-title'> <?php esc_attr_e('Page Title:', 'text_domain'); ?></label> 
		<input class="widefat title" id="pagetitle" name="page_title" type="text" value="<?= esc_attr($title) ?>">
		<?php
		
	}
    
    private function admin_path() {
		
		$path = empty($this->landing_page->path) ? "": $this->landing_page->path;
		
		?>
		<label for='page-path'> <?php esc_attr_e('Url Path:', 'text_domain'); ?></label> 
		<input class="widefat title" id="pagepath" name="pagepath" type="text" value="<?= esc_attr($path) ?>">
		<?php
		
	}
    
	private function admin_credits() {
		
		$credits = !empty($this->landing_page->credits) ? $this->landing_page->credits : esc_html__('', 'text_domain');
		

		?>
        <!-- Descriptive Title text.  "Credits" -->
        <label for="pagecredits"><?php esc_attr_e('Credits:', 'text_domain'); ?></label> 
        <textarea class="widefat credits" id="pagecredits>" name="credits"><?= $credits ?></textarea>
		<?php
	}
	
	private function admin_meta_description(){
		
		$description = ! empty( $this->landing_page->meta_description ) ? $this->landing_page->meta_description : esc_html__( '', 'text_domain' );
		?>
		<label for="meta_description"><?php esc_attr_e( 'Description (for meta):', 'text_domain' ); ?></label> 
        <textarea class="widefat meta_description" id="meta_description" name="meta_description"><?php echo $description;?></textarea>
		<?php
	}
    
    private function admin_image_url(){
		
		
		$image_url = $this->landing_page->get_image_url();
		
		?>
		<label for="image_url"><?php esc_attr_e( 'Image Url:', 'text_domain' ); ?></label> 
		<input class="widefat title" id="image_url" name="image_url" type="text" value="<?php echo esc_attr( $image_url ); ?>">
		<?php
		
	}
	
	private function admin_department(){
		
        $department = $this->landing_page->get_department();
		
		?>
        <label for="department"><?php esc_attr_e( 'Department:', 'text_domain' ); ?></label>
		<?php
		
		
		
		if (!isset($department)) {
			$department = false;
		}
		
		echo $this->makeSelect( "department", "department", $this->departments, $department, array("widefat", "department"));?>
		
		<?php
		
	}
	
	
	/* Here's where today's work is*/
	private function admin_designation(){
		
		if(0){ //  back check for page_type.
			   //   1.  What is stub?  it just writes a hidden field with a static value for the homepage.
			   //   2.  how do we check for current page type $this->page_type=='homepage'){
			   //   3.  how can this be done better
			   
			echo $this->stub($this->get_field_id('feature-designation'),$this->get_field_name('feature-designation'),'general');
		}
		else{
			
			$feature_designation = $this->landing_page->get_feature_designation();
            
			$department = $this->landing_page->get_department();
			
			?>
			 <label for="feature-designation"><?php esc_attr_e( 'Feature Designation:', 'text_domain' ); ?> <i>to do:  stub this for homepage page type</i></label>
			<?php
			$options = array("department_latest"=>"Department Latest","department_feature"=>"Department Feature","category"=>"Department Category Post","series"=>"Department Series Post");
			echo $this->makeSelect( 'feature-designation' , 'feature-designation' , $options, $feature_designation, array("widefat", "feature-designation"));
			
			
			if($feature_designation != "department_feature"){
				$feature_category = $this->landing_page->get_feature_category();
                
				switch($feature_designation){
					case "category":
						?>
							<?php
							echo $this->makeCategorySelect($department, $feature_designation, $feature_category,  'feature-category' ,  'feature-category' , array("widefat", "feature-category"));
						break;
					case "series":
						?>
			<?php
			echo $this->makeCategorySelect($department, $feature_designation, $feature_category, 'feature-category',  'feature-category', array("widefat", "feature-category"));
						break;
					default:
						break;
				}
			}
		}
		
		
	}
    
    private function admin_grid(){
		
		$grid = ! empty( $this->landing_page->grid) ? $this->landing_page->grid : array();
		
		?>
		<ul id="grid">
            <?php foreach($grid as $cell):
            
                $cell_type_select = $this->makeCelltypeSelect($cell->type);
				
				switch($cell->type){
					case "special-series":
						$designation_select = $this->makeDesignationSelect($cell->designation);
						$taxonomy_select = "<input type='hidden' name='cell-taxonomy[]' id='taxonomy' value='{$cell->taxonomy}' />";
						$category_select = $this->makeCategorySelect($cell->designation,$cell->taxonomy,$cell->category);
                        $cell_header  = "<input type='hidden' name='cell-header[]' id='cell-header' value='' />";
                        $cell_subheader  = "<input type='hidden' name='cell-subheader[]' id='cell-subheader' value='' />";
                        $header_div = "<div id='headersubheader' class='row'>".$celL_header.$cell_subheader."</div>";
                        $image1 =  "<input type='hidden' name='image1[]' id='image1' value='' />";
                        $image2 =  "<input type='hidden' name='image2[]' id='image2' value='' />";
                        $image3 =  "<input type='hidden' name='image3[]' id='image3' value='' />";
                        $images_div = "<div id='images' class='row'>".$image1.$image2.$image3."</div>";
                        
                        $link1  = "<input type='hidden' name='link1[]' id='link1' class='cell-head' value='' />";
                        $link2  = "<input type='hidden' name='link2[]' id='link2' class='cell-head' value='' />";
                        $link3  = "<input type='hidden' name='link3[]' id='link3' class='cell-head' value='' />";
                        $links_div = "<div id='links' class='row'>".$link1.$link2.$link3."</div>";
                        
                        $buttons_div = "";
                        $cell_description = "<input type='hidden' name='cell-description[]' id='cell-description' value='' />";
                        $description_div = "<div id='description' class='row'>".$cell_description."</div>";
						break;
					case "special-topartists":
						$designation_select = "<input type='hidden' name='designation[]' id='designation' value='{$cell->designation}' />";
						$taxonomy_select = "<input type='hidden' name='cell-taxonomy[]' id='taxonomy' value='{$cell->taxonomy}' />";
						$category_select = "<input type='hidden' name='cell-category[]' id='category' value='{$cell->category}' />";
                        $cell_header  = "<input type='hidden' name='cell-header[]' id='cell-header' value='' />";
                        $cell_subheader  = "<input type='hidden' name='cell-subheader[]' id='cell-subheader' value='' />";
                        $header_div = "<div id='headersubheader' class='row'>".$celL_header.$cell_subheader."</div>";
						break;
                    case "homepage-feature":
                        
                        $designation_select = "<input type='hidden' name='designation[]' id='designation' value='{$cell->designation}' />";
                        $taxonomy_select = "<input type='hidden' name='cell-taxonomy[]' id='taxonomy' value='{$cell->taxonomy}' />";
						$category_select = "<input type='hidden' name='cell-category[]' id='category' value='{$cell->category}' />";
                        $cell_header  = "<input type='text' name='cell-header[]' id='cell-header' class='cell-head' value='{$cell->header}' />";
                        $cell_subheader  = "<input type='text' name='cell-subheader[]' id='cell-subheader' class='cell-head' value='{$cell->subheader}' />";
                        $header_div = "<div id='headersubheader' class='row'>".$cell_header.$cell_subheader."</div>";
                        
                        $image1  = "<input type='text' name='image1[]' id='image1' class='image-link' value='{$cell->image1}' placeholder='image 1'/>";
                        $image2  = "<input type='text' name='image2[]' id='image2' class='image-link' value='{$cell->image2}' placeholder='image 2'/>";
                        $image3  = "<input type='text' name='image3[]' id='image3' class='image-link' value='{$cell->image3}' placeholder='image 3'/>";
                        $images_div = "<div id='images' class='row'>".$image1.$image2.$image3."</div>";
                        
                        $button1  = "<button id='imagebutton1' data-for='image1 'class='add-image' value='add-image'/>Add image</button>";
                        $button2  = "<button id='imagebutton2' data-for='image2' class='add-image' value='add-image'/>Add image</button>";
                        $button3  = "<button id='imagebutton3' data-for='image3' class='add-image' value='add-image'/>Add image</button>";
                        
                        $buttons_div = "<div id='image_butons' class='row'>".$button1.$button2.$button3."</div>";
                        
                        $link1  = "<input type='text' name='link1[]' id='link1' class='image-link' value='{$cell->link1}' placeholder='link 1'/>";
                        $link2  = "<input type='text' name='link2[]' id='link2' class='image-link' value='{$cell->link2}' placeholder='link 2'/>";
                        $link3  = "<input type='text' name='link3[]' id='link3' class='image-link' value='{$cell->link3}' placeholder='link 3' />";
                        $links_div = "<div id='links' class='row'>".$link1.$link2.$link3."</div>";
                        
                        
                        $cell_description = "<textarea name='cell-description[]' id='cell-description' class='cell-description'>{$cell->description}</textarea>";
                        $description_div = "<div id='description' class='row'>".$cell_description."</div>";
                        
                        break;
					default:
						$designation_select = $this->makeDesignationSelect($cell->designation);
						$taxonomy_select = $this->makeTaxonomySelect($cell->designation, $cell->taxonomy);
                        $category_select = $this->makeCategorySelect($cell->designation,$cell->taxonomy,$cell->category);
                        
                        
                        $cell_header  = "<input type='hidden' name='cell-header[]' id='cell-header' value='' />";
                        $cell_subheader  = "<input type='hidden' name='cell-subheader[]' id='cell-subheader' value='' />";
                        $header_div = "<div id='headersubheader' class='row'>".$cell_header.$cell_subheader."</div>";
                        
                        $image1 =  "<input type='hidden' name='image1[]' id='image1' value='' />";
                        $image2 =  "<input type='hidden' name='image2[]' id='image2' value='' />";
                        $image3 =  "<input type='hidden' name='image3[]' id='image3' value='' />";
                        $images_div = "<div id='images' class='row'>".$image1.$image2.$image3."</div>";
                        
                        $link1  = "<input type='hidden' name='link1[]' id='link1' class='cell-head' value='' />";
                        $link2  = "<input type='hidden' name='link2[]' id='link2' class='cell-head' value='' />";
                        $link3  = "<input type='hidden' name='link3[]' id='link3' class='cell-head' value='' />";
                        $links_div = "<div id='links' class='row'>".$link1.$link2.$link3."</div>";
                        
                        $buttons_div = "";
                        
                        $cell_description = "<input type='hidden' name='cell-description[]' id='cell-description' value='' />";
                        $description_div = "<div id='description' class='row'>".$cell_description."</div>";
                        
                        if(empty($cell->taxonomy)){
                            diebug($cell);
                        }
                        
						break;
				}
				
                $class = "cell_" . $cell->type;
            ?>
                <li class="<?php echo $class;?>">
                <div class='delete-cell'>&#9447;</div>
                <div class="cell-wrap">
                    <?php echo $cell_type_select;?>
                    <?php echo $designation_select;?>
                    <?php echo $taxonomy_select;?>
                    <?php echo $category_select;?>
                    <?php echo $header_div;?>
                    <?php echo $images_div;?>
                    <?php echo $buttons_div;?>
                    <?php echo $links_div;?>
                    <?php echo $description_div;?>
                </div>
                </li>
            <?php endforeach;?>
        </ul>
        <div style="clear:both"></div>
        <button id="add-cell">Add</button>
		<?php
		
	}
	
	
    public function save_post($post_id,$post,$update){
        
        if ( (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) || wp_is_post_revision($post_id) || !$update )
            return $post_id;    
        
        
        if ( $post->post_type != 'landing') 
            return $post_id;
        
        $landing_page = new BTRtoday_Landing_Page($post);        
        $landing_page->set_page_type($_REQUEST['landing-page-type']);
        
        $landing_page->set_page_category(empty($_REQUEST['page-category'])?"":$_REQUEST['page-category']);
        $landing_page->set_masthead_title($_REQUEST['masthead_title']);
        $landing_page->set_page_title($_REQUEST['page_title']);
        $landing_page->set_credits($_REQUEST['credits']);
        $landing_page->set_meta_description($_REQUEST['meta_description']);
        $landing_page->set_image_url($_REQUEST['image_url']);
        $landing_page->set_department($_REQUEST['department']);
        $landing_page->set_feature_designation($_REQUEST['feature-designation']);
        $landing_page->set_feature_category($_REQUEST['feature-category']);
        
        $landing_page->set_path($_REQUEST['pagepath']);
        
		#unpack the grid forms
		
		$grid = array();
		for($i=0;$i<count($_REQUEST['cell-type']);$i++){
            
			$cell = new stdClass();
			$cell->type = $_REQUEST['cell-type'][$i];
			$cell->designation = $_REQUEST['cell-designation'][$i];
			$cell->taxonomy = $_REQUEST['cell-taxonomy'][$i];
			$cell->category = $_REQUEST['cell-category'][$i];  #TBD = Change "category" to "slug" or a better word than "slug"
            $cell->header = $_REQUEST['cell-header'][$i];
            $cell->subheader = $_REQUEST['cell-subheader'][$i];
            $cell->image1 = $_REQUEST['image1'][$i];
            $cell->image2 = $_REQUEST['image2'][$i];
            $cell->image3 = $_REQUEST['image3'][$i];
            $cell->link1 = $_REQUEST['link1'][$i];
            $cell->link2 = $_REQUEST['link2'][$i];
            $cell->link3 = $_REQUEST['link3'][$i];
            
            $cell->description = $_REQUEST['cell-description'][$i];
            
            
			$grid[] = $cell;
		}
        
        $landing_page->set_grid($grid);
		
        $landing_page->save();
        
    }
	
	private function makeSelect($id,$name,$options,$select_value=false,$classes=null){

		$class = '';
        if(!empty($classes)){
            if(is_array($classes)){
                $class = implode(" ",$classes);
            }
            else{
                $class = $classes;
            }
        }

        $select="<select class='{$class}' id='{$id}' name='{$name}'>";
        
        foreach($options as $key=>$val){
            $selected=($key==$select_value)?"selected":"";
            $select.= "<option value='{$key}' {$selected}>{$val}</option>";
        }
        
        $select.="</select>";
        return $select;
    }
	
	private function makeCategorySelect( $dept , $taxonomy , $selected=false , $id="cell-category" , $name="cell-category[]" , $classes=null){
		
        switch($taxonomy){
			case "post_tag":
				return "<input type='text' value='{$selected}' name='cell-category[]' id='category' />";
				break;
			case "department_latest":
				return "<input type='hidden' value='n/a' name='cell-category[]' id='category' />";
				break;
            case "series":
                    switch ($dept) {
                        case "listen":
                            $terms = get_terms("podcast-series");
                            break;
                        case "read":
                            $terms = get_terms("editorial-section");
                            break;
                        case "tv":
                            $terms = get_terms("video-series");
                            break;
                    }
                break;
				case "category":
                    $terms = get_terms("category");
                break;
            default:
                diebug($taxonomy);
                break;
        }
        $options = array();
		
		
		
		// first get all the parents to the top
		// O(n)
		$terms = sort_terms_hierarchically($terms);
		
		
		// NOW go through them and MAKE THE OPTIONS
		$options = array();
		foreach($terms as $term){
			$text = $term->name;
			if($term->parent>0){
				$text = " - " . $text;
			}
			$options[$term->slug] = $text;
		}
        
        return $this->makeSelect($id,$name,$options,$selected,$classes);
    }
	
	
		
	private function makeTaxonomySelect($designation,$selected=false){
		
		$options = array("category"=>"Category","post_tag"=>"Tag", "department_latest"=>"Department Latest");
		if($designation!="general"){
			$options["series"] = "Series";
		}
        return $this->makeSelect("cell-taxonomy","cell-taxonomy[]",$options,$selected);
	}
	
    
    private function makeDesignationSelect($selected=false){
        $options = array("general"=>"General","listen"=>"Listen","read"=>"Read","tv"=>"Watch");  
        return $this->makeSelect("cell-designation","cell-designation[]",$options,$selected);
    }
    
    
    private function makeCelltypeSelect($selected=false){
        
        return $this->makeSelect("cell-type","cell-type[]",$this->cell_types,$selected);
    }
    
	private function makeDepartmentSelect($selected=false){
        
        $options = array("listen"=>"Listen", "read"=>"Read","tv"=>"Watch");
        return $this->makeSelect("department","cell-department[]",$options,$selected);
	
    }
	
    
    private function get_landing_pages(){
        global $wpdb;
        $sql = "SELECT * from wp_posts WHERE post_type='landing' AND post_status = 'publish'";
        $results = $wpdb->get_results($sql);
        $return = array();
        foreach($results as $result){
            $return[] = new BTRtoday_Landing_Page($result);
        }
        return $return;
        
    }
    
   
    
    private function convert_grid($old_grid){
        if(empty($old_grid)){return;}
        $grid = array();
        foreach($old_grid as $old_cell){
            $cell = new stdClass();
            
            $cell->type = $old_cell->cell_type;
            $cell->designation = $old_cell->designation;
            $cell->taxonomy = $old_cell->taxonomy;
            $cell->category = $old_cell->category;
            
            $grid[] = $cell;
        }
        
        return $grid;
    }
   
    public function get_page($slug){
        global $wpdb;
        $sql = $wpdb->prepare("SELECT * from wp_posts WHERE post_name='%s' AND post_type='landing' AND post_status='publish'",$slug);
        $post = $wpdb->get_results($sql);
        if(empty($post)){
            return false;
        }
        
        return new BTRtoday_Landing_Page($post[0]);
    }
	
}

/**
 * BTR Landing Page Class
 */
class BTRtoday_Landing_Page extends BTRtoday_Page{
	
	private $post;
    public $page_type;
	public $page_title;
    public $page_category;
    public $masthead_title;
	public $credits;
	public $meta_description;
	public $department;
	public $grid;
    public $image_url;
    public $path;
    
    private $featured_post;
    private $feature_designation;
    private $feature_category;
    private $page_posts;
	private $cells_rendered;
	private $page_types;
	private $cell_types;
	private $designations;
    
	/**
	 * Register widget with WordPress.
	 */
	public function __construct( $post  ) {
		
		$this->set_designations();
		$this->set_cell_types();
        $this->post = $post;
        $this->page_type = get_post_meta($post->ID, 'page_type', true);
        $this->page_category = get_post_meta($post->ID, 'page_category', true);
        $this->masthead_title = get_post_meta($post->ID, 'masthead_title', true);
        $this->page_title = get_post_meta($post->ID, 'page_title', true);
        $this->credits = get_post_meta($post->ID, 'page_credits', true);
        $this->meta_description = get_post_meta($post->ID, 'meta_description', true);
        $this->image_url = get_post_meta($post->ID, 'image_url', true);
        $this->department = get_post_meta($post->ID, 'department', true);
        $this->feature_designation = get_post_meta($post->ID, 'feature_designation', true);
        $this->feature_category = get_post_meta($post->ID, 'feature_category', true);
		$this->grid = get_post_meta($post->ID, "grid", true);
        $this->path = get_post_meta($post->ID, "path",true);
        
	}

	/**
	 * Front-end display of widget.
	 *
	 * @see WP_Widget::widget()
	 *
	 * @param array $args     Widget arguments.
	 * @param array $instance Saved values from database.
	 */
	
	/*
     * different page types for page_type dropdowns
     * is this necessary?
	*/
	
	
    /* for accent colors, etc */
	private function set_designations(){
		$this->designations = array(
			"listen" => "Listen",
			"read" => "Read",
			"tv" => "Watch"
		);
	}
    
    /* here's where we set our cell types */
	private function set_cell_types(){
		$this->cell_types = array(
			"1x1" => "1x1",
			"2x2" => "2x2",
			"3x1" => "3x1",
			"3x3" => "3x3",
			"3x6" => "3x6",
			"special-series" => "Series",
			"special-topartists" => "Top Artists",
		);
	}
	

	private function set_instance($instance) {
		$this->validate_instance($instance);
		$this->instance = $instance;
	}
	
    public function save(){
        
        update_post_meta($this->post->ID, 'page_type', $this->page_type);
        update_post_meta($this->post->ID, 'page_category', $this->page_category);
        update_post_meta($this->post->ID, 'page_title', $this->page_title);
        update_post_meta($this->post->ID, 'masthead_title', $this->masthead_title);
        update_post_meta($this->post->ID, 'path', $this->path);
        update_post_meta($this->post->ID, 'page_credits', $this->credits);
        update_post_meta($this->post->ID, 'meta_description', $this->meta_description);
        update_post_meta($this->post->ID, 'image_url', $this->image_url);
        update_post_meta($this->post->ID, 'department', $this->department);
        update_post_meta($this->post->ID, 'feature_designation', $this->feature_designation);
        update_post_meta($this->post->ID, 'feature_category', $this->feature_category);
        update_post_meta($this->post->ID, 'grid', $this->grid);
        
    }
    
    
    public function get_page_type(){
        return $this->page_type;
    }
    
    public function set_page_type($pagetype){
        $this->page_type = $pagetype;
    }
	
    public function get_page_category(){
        return $this->page_category;
    }
    
    public function set_page_category($category){
        $this->page_category = $category;
        
    }
    
    public function get_page_title(){
        return $this->page_title;
    }
    
    public function set_page_title($title){
        $this->page_title = $title;
    }
    
    public function get_masthead_title(){
        return $this->masthead_title;
    }
    
    public function set_masthead_title($title){
        $this->masthead_title = $title;
    }
    public function get_path(){
        return $this->path;
    }
    
    public function set_path($path){
        $this->path = $path;
    }
    
    public function get_credits(){
        return $this->credits;
    }
    
    public function set_credits($credits){
        $this->credits = $credits;
    }
    
    public function get_meta_description(){
        return $this->meta_description;
    }
    
    public function set_meta_description($description){
        $this->meta_description = $description;
    }
    
    public function get_image_url(){
        return $this->image_url;
    }
    
    public function set_image_url($url){
        $this->image_url = $url;
    }
    
    public function get_department(){
        return $this->department;
    }
    
    public function set_department($department){
        $this->department = $department;
    }
    
    public function get_feature_designation(){
        return $this->feature_designation;
    }
    
    public function set_feature_designation($designation){
        $this->feature_designation = $designation;
    }
    
    public function get_feature_category(){
        return $this->feature_category;
    }
    
    public function set_feature_category($category){
        $this->feature_category = $category;
    }
	
	public function set_grid($grid){
		$this->grid = $grid;
	}
	
	public function get_grid($grid){
		return $this->grid;
	}
	
	public function render() {
        
		?>
		<div id="root" class="landing-page <?= $this->department ?>">
			<?php if ($this->page_type !== "homepage"): ?>
				<?php $this->render_masthead(); ?>
				<?php $this->render_featured_post(); ?>
			<?php endif; ?>
			<?php $this->render_grid(); ?>
		</div> <!-- #landing -->
		<?php
	}
	
	// Title and Credits
	private function render_masthead() {
		$lines = preg_split("/<[Bb][Rr]\s?\/?>/", $this->credits);
		$class = "two";
		if (is_array($lines) && count($lines) === 3) {
			$class = "three";
		}
		?>
		<div class="section">
			<div id="masthead" class="contents group topborder <?= $this->department ?>">
				<div id="masthead-left" class="taxonomy label accent <?= $this->department ?>"><?= $this->masthead_title ?></div>
				<div id="masthead-right" class="<?= $class ?>"><?= $this->credits ?></div>
			</div> <!-- #masthead -->
		</div>
	   <?php
	}
	
    private function set_featured_post() {
        
        switch ($this->feature_designation) {
            
			case "department_latest":
				$post = $this->query_latest_department_post($this->department);
				break;
            case "department_feature":
                $post = $this->query_featured_department_feature($this->department);
                break;
            case "category":
                $post = $this->query_featured_department_category_post($this->department, $this->feature_category);
                break;
            case "series":
				$post = $this->query_featured_department_series_post($this->department, $this->feature_category);
                break;
            
			default:
				break;
        }
        
		if ($post) {
			// Add ID to list for no repeats
			$this->featured_post = $post;
			$this->page_posts[] = $post->ID;
		}
        
    }
    
    private function get_featured_post() {
        return $this->featured_post;
    }
	
	private function render_featured_post() {
       
	   	$this->set_featured_post();
	   
	   	if ($this->featured_post): ?>
			<div id="banner" class="feature-box page-banner padded"> <!-- this gives us the background layer -->
				<div class="section">
					<?php render_featured_post($this->featured_post, array(
						'label' => 'Featured',
						'excerpt_length' => 150
					)); ?>
				</div> <!-- section -->
			</div> <!-- banner -->
        <?php endif;
	}

	private function query_single($query) {
		global $wpdb;
		$args = func_get_args();
    	array_shift($args);
		$sql = $wpdb->prepare($query, $args);
		$results = $wpdb->get_results($sql);
		if (is_array($results) && count($results) === 1) {
			return postify($results[0]);
		}
		return false;
	}
	
	private function query_latest_department_post($department) {
		return $this->query_single("SELECT
				*
			FROM
				wp_posts p
			WHERE
				post_type = '%s'
			AND post_status = 'publish'
			ORDER BY
				post_date desc
			LIMIT 1", $department);
	}
	
    private function query_featured_department_feature($department){
        return $this->query_single("SELECT
                *
            FROM
                wp_posts p
            JOIN wp_postmeta pm ON p.ID = pm.post_id
            WHERE
                post_type = '%s'
            AND post_status = 'publish'
            AND pm.meta_key = 'department_feature' 
            AND pm.meta_value = '1'
            ORDER BY
                post_date desc
            LIMIT 1", $department);
    }
    
    private function query_featured_department_category_post($department, $category) {
		if ($department == "general") {
			$department = implode("','", array('tv', 'listen', 'read'));
		}
		return $this->query_single("SELECT
                *
            FROM
                wp_posts p
            JOIN wp_postmeta pm ON p.ID = pm.post_id
            JOIN wp_term_relationships tr on tr.object_id = p.ID
            JOIN wp_term_taxonomy tt on tt.term_taxonomy_id = tr.term_taxonomy_id
            JOIN wp_terms t on t.term_id = tt.term_id
            WHERE
                post_type in ('{$department}')
            AND post_status = 'publish'
            AND pm.meta_key = 'department_feature' 
            AND pm.meta_value = '1'
            AND t.slug='%s'
            ORDER BY
                post_date desc
            LIMIT 1", $category);
    }
    
    
    private function query_featured_department_series_post($department, $category) {
		$tax = "category";
		switch ($department) {
			case "listen":
				$tax = "podcast-series";
				$meta = "podcast_series";
				break;
			case "read":
				$tax = "editorial_section";
				$meta = "editorial_section";
				break;
			case "tv":
				$tax = "video-series";
				$meta = "video_series";
				break;
		}
		$term = get_term_by("slug", $category, $tax);

		return $this->query_single("SELECT *
			FROM (
				SELECT
					p.*
				FROM
					wp_posts p
				JOIN
					wp_postmeta pm ON p.ID = pm.post_id
				WHERE
					pm.meta_key = '%s' 
				AND	pm.meta_value = '%s'
				AND p.post_status = 'publish'
				AND p.post_type = '%s'
				ORDER BY post_date DESC
			) as posts
			JOIN
				wp_postmeta postmeta ON posts.ID = postmeta.post_id
			WHERE 
				postmeta.meta_key='department_feature'
			AND postmeta.meta_value='1'
			ORDER BY post_date DESC
			LIMIT 1", $meta, $term->term_id, $department);
    }
        
    
    private function render_grid() {
		$grid = $this->grid;
        
                foreach($grid as $i=>$cell):
                    if($i==0):
                        if(strpos($cell->type,'feature')>-1){
                            $class = strpos($cell->type,'feature')>-1?'page-banner':'';
                            }
                    endif;
                    ?>
        <div class="section <?php echo $class;?>">
			<div id="grid" class="grid group">
                <?php
                    
					$this->render_cell($cell); 
                    if ($this->columns_rendered % 3 ==0 ):
                    ?>
            </div>
        </div>
        <div class="section">
			<div id="grid" class="grid group">
                    <?php
                    endif;
				endforeach; ?>
			</div> <!-- #grid -->
		</div> <!-- .section -->
		<?php
    }
	
    
    private function render_cell($cell) {
        
		if (strpos($cell->type, "x") <= -1) {
			if ($cell->type === 'special-series') {
				$this->render_special_series($cell);
			} elseif ($cell->type === 'special-topartists') {
				$this->render_special_topartists($cell);
			} elseif ($cell->type === 'homepage-feature') {
                $this->render_homepage_feature($cell);
            }
			return;
		}
		$posts = $this->get_cell_posts($cell);
        
		$term = $this->get_cell_taxonomy($cell->designation, $cell);
		
		list($columns, $items) = $this->parse_cell_type($cell->type);

		$wide = "";
		if ($columns == 3 && $items == 1) {
			$wide = "wide";
		}

		?>
		<div class="cell col_<?= $columns ?>">
			<div class="<?= $cell->designation ?>">
				<a href="<?= $term->url ?>" class="<?= $cell->designation ?>"><h5 class="head contents topborder accent <?= $cell->designation ?>"><?= $term->name ?></h5></a>
			</div>	
			<?php foreach($posts as $i => $post): ?>
				<?php render_post_grid_item($post, array(
					'title_length' => 55,
					'class' => $wide
				)); ?>
			<?php endforeach; ?>
		</div>
		<?php
    }
	
	
	private function render_special_series($cell) {
		
		$term = $this->get_cell_taxonomy($cell->designation, $cell);
	
		$posts = $this->get_cell_posts($cell);
		$series = $posts[0]->series;
		?>
		<div class="cell <?= $cell->type ?>">
			<div class="<?= $cell->designation ?>">
				<a href="<?= $term->url ?>" class="<?= $cell->designation ?>"><h5 class="head contents topborder accent <?= $cell->designation ?>"><?= $term->name ?></h5></a>
			</div>
			<div class="item wide">
				<div id="series_image">
					<a href="<?= $term->url ?>"><img src="<?= $series->podcast_open_graph_image ?>"/></a>
				</div>
				<div id="posts">
					<?php foreach($posts as $post): ?>
					<div class="post">
						<div class="title"><a href="<?= $post->permalink ?>" class="<?= $cell->designation ?>"><?= $post->post_title ?></a></div>
						<div class="date"><?= $post->formatted_date_day ?></div>
					</div>
					<?php endforeach; ?>
				</div>
			</div>
		</div>
		<?php
	}
	
	private function render_special_topartists($cell) {
		$this->columns_rendered++;
		$bump = ($this->columns_rendered % 3 != 0) ? "bump" : "";
		$artists = get_option("btrtoday-topartists");
		?>
		<div class = "cell col_1 <?= $bump ?>">
			<div id="head" class="head designation category accent topborder general"><a href="listen/" class="general">Top Artists</a></div>
			<?php foreach($artists as $i=>$artist_name):
				$artist = get_term_by("name", $artist_name, "artist");
				$artist = artistify($artist); ?>
				<div id="posts">
					<div class="post">
						<div class="data">no<?= $i + 1 ?></div>
						<div class="title"><a href="/artist/<?= $artist->slug ?>"><?= $artist->name ?></a></div>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
		<?php
	}
    
    private function render_homepage_feature($cell){
        
		?>
		<div class="cell <?= $cell->type ?> page-banner padded">
			<div class="<?= $cell->designation ?>">
				here's where I'm putting the header
			</div>
			<div class="item wide">
				here's where we are putting the stuff
			</div>
		</div>
        
        <?php
    }
    
    private function get_cell_taxonomy($department, $cell) {
        switch ($cell->taxonomy) {
            case "series":
                switch ($cell->designation) {
                    case "listen":
                        $tax = "podcast-series";
						$term = get_term_by("slug", $cell->category, $tax);
                        break;
                    case "tv":
                        $tax = "video-series";
						$term = get_term_by("slug", $cell->category, $tax);
                        break;
                    case "read":
                        $tax = "editorial-section";
						$term = get_term_by("slug", $cell->category, $tax);
                        break;
                    default:
                        $tax= "category";
						break;
                    break;
				}
				$url = BASEURL . "/{$department}/{$term->slug}/";
                break;
            case "category":
				$tax = "category";
				$term = get_term_by("slug", $cell->category, $tax);
				$url = BASEURL . "/category/{$term->slug}/";
				break;
            case "post_tag":
                $tax = "post_tag";
				$term = get_term_by("slug", $cell->category, $tax);
				$url = BASEURL . "/tag/{$term->slug}/";
                break;
			case "department_latest":
				$term = new stdClass();
				$term->name = $this->designations[$cell->designation];
				$url = BASEURL . "/{$cell->designation}/";
				break;
        }
		$term->url = $url;
        return $term;
    }
    
    private function get_cell_posts($cell) {      
		global $wpdb;
	
		list($columns, $items) = $this->parse_cell_type($cell->type);
		if (!is_numeric($items)) {
			switch ($items) {
				case "series":
				case "topartists":
					$items = 4;
					break;
			}
		}
		
		// Get post taxonomy for query
		$tax = "";
		$term = null;
      
		switch ($cell->taxonomy) {
			case "series":
				$tax = "category";
				$type = array($cell->designation);
				switch ($cell->designation) {
					case "tv":
						$tax = "video-series";
						$type = "video_series";
						$meta = "video_series";
						break;
					case "listen":
						$tax = "podcast-series";
						$type = "podcast_series";
						$meta = "podcast_series";
						break;
					case "read":
						$tax = "editorial-section";
						$type = "editorial_section";
						$meta = "section_name";
						break;
				}
				$term = get_term_by("slug",$cell->category,$tax);
				break;
			case "category":
			case "post_tag":
                
				$type = $cell->designation;
				$tax = $cell->taxonomy;
				if ($cell->designation == "general") {
					$type = implode("','", array("read", "listen", "tv"));
				}	
				$term = get_term_by("slug", $cell->category, $tax);
				break;
            default:
                    $tax = "";
					$term = null;
                    break;
		}
	
		$in = $this->page_posts_filter();
	
		switch ($cell->taxonomy) {
			case "category":
			case "post_tag":
				$sql = $wpdb->prepare("SELECT
						DISTINCT(p.ID), p.*
					FROM
						wp_posts p
					JOIN wp_term_relationships tr ON tr.object_id = p.ID
					JOIN wp_term_taxonomy tt ON tt.term_taxonomy_id = tr.term_taxonomy_id
					JOIN wp_terms t ON t.term_id = tt.term_id
					WHERE
						p.post_status='publish'
					AND p.post_type IN('{$type}')
					{$in}
					AND t.slug = '%s'
					AND tt.taxonomy = '%s'
					ORDER BY
						p.post_date DESC
					LIMIT 0, %d",
					$cell->category,
					$tax,
					$items
				);
				break;
			case "series":
				$sql = $wpdb->prepare("SELECT
						DISTINCT(p.ID),
						p.*
					FROM
						wp_posts p
					JOIN wp_postmeta pm ON p.ID = pm.post_id
					WHERE
						pm.meta_key = '%s'
					AND pm.meta_value = '%s'
					AND p.post_status = 'publish'
					AND p.post_type = '%s'
					{$in}
					ORDER BY
						post_date DESC
					LIMIT 0, %d",
					$meta, $term->term_id, $cell->designation, $items
				);
				break;
			case "department_latest":
               
				$join = "";
				$term = "";
                
				if ($this->page_type == "category_landing") {
					$join = " JOIN wp_term_relationships tr ON p.ID = tr.object_id JOIN wp_term_taxonomy tt on tt.term_taxonomy_id = tr.term_taxonomy_id JOIN wp_terms t on t.term_id = tt.term_id ";
					$term = " AND t.slug = '{$this->page_category}' ";
				}
				$sql = $wpdb->prepare("SELECT
						DISTINCT(p.ID), p.*
					FROM
						wp_posts p
					$join
					WHERE
						p.post_status = 'publish'
					AND p.post_type = '%s'
					$term
					{$in}
					ORDER BY
						post_date DESC
					LIMIT 0, %d",
					$cell->designation, $items
				);
				break;
		}

		$return = array();		
		$posts = $wpdb->get_results($sql);
		foreach ($posts as $i=>$post) {
			$return[] = postify($post);
			$this->page_posts[] = $post->ID;
		}
		return $return;
	}
    
    private function makeSelect($id,$name,$options,$select_value=false,$classes=null){

		$class = '';
        if(!empty($classes)){
            if(is_array($classes)){
                $class = implode(" ",$classes);
            }
            else{
                $class = $classes;
            }
        }

        $select="<select class='{$class}' id='{$id}' name='{$name}'>";
        
        foreach($options as $key=>$val){
            $selected=($key==$select_value)?"selected":"";
            $select.= "<option value='{$key}' {$selected}>{$val}</option>";
        }
        
        $select.="</select>";
        return $select;
    }
	
	// output a hidden input with a specified ID, NAME, and VALUE
	private function stub($id,$name,$value){
		return "<input type='hidden' id='$id' name='$name' value='$value' />";
	}
    
	// Note:  cell type desginations are arbitrary
    private function parse_cell_type($type){
		if(strpos($type,"x")>-1)
			return explode("x",$type);
		else{
			return explode("-",$type);
		}
    }
    
    public function page_posts_filter() {
		if (is_array($this->page_posts) && count($this->page_posts)) {
			return " AND p.ID NOT IN (" . "'" . join("','", $this->page_posts) . "'" . ") ";
		} else {
			return "";
		}
	}
	

} 
