<?php

class KameleonStart extends Doctrine_Migration_Base {
    protected $conn;

    public function up() {
        $this->conn=Doctrine_Manager::connection();
        
        $old=false;
        try {
            $webtd=$this->conn->fetchOne('SELECT count(*) FROM kameleon');
            $old=true;
        }
        catch (Exception $e) {
            $this->conn->rollback();
            $this->conn->beginTransaction();
            $this->newKameleonUp();
        }
        if ($old) $this->oldKameleonUp();
    }
    
    
    
    public function down() {
        $this->conn=Doctrine_Manager::connection();

        $old=false;
        try {
            $webtd=$this->conn->fetchOne('SELECT count(*) FROM kameleon');
            $old=true;
        }
        catch (Exception $e) {
            $this->conn->rollback();
            $this->conn->beginTransaction();            
            $this->newKameleonDown();
        }        
        if ($old) $this->oldKameleonDown();
    }
    
    
    
    
    protected function newKameleonUp() {
        
        

        $int2=Doctrine_Manager::connection()->getDbh()->getAttribute(PDO::ATTR_DRIVER_NAME)=='pgsql'?'int2':'integer(2)';

        

      $this->createTable('ftp',array(
             'id'=>array('type'=>'integer','autoincrement' => true,'notnull' => true, 'primary'=>true),
             'server'=>array('type'=>'integer'),
             'username'=>array('type'=>'varchar(32)'),
             't_begin'=>array('type'=>'integer'),
             't_end'=>array('type'=>'integer'),
             'killed'=>array('type'=>'character(1)'),
             'ver'=>array('type'=>$int2),
             'pid'=>array('type'=>'integer'),
             'lang'=>array('type'=>'character(2)'),
             't_start'=>array('type'=>'integer'),
        ));
      
      $this->createTable('ftp_arch',array(
             'id'=>array('type'=>'integer'),
             'server'=>array('type'=>'integer'),
             'username'=>array('type'=>'varchar(32)'),
             't_begin'=>array('type'=>'integer'),
             't_end'=>array('type'=>'integer'),
             'ver'=>array('type'=>$int2),
             'pid'=>array('type'=>'integer'),
             'lang'=>array('type'=>'character(2)'),
        ));
      $this->createTable('ftplog',array(
             'id'=>array('type'=>'integer','autoincrement' => true,'notnull' => true, 'primary'=>true),
             'ftp_id'=>array('type'=>'integer'),
             'rozkaz'=>array('type'=>'text'),
             'wynik'=>array('type'=>'text'),
             'nczas'=>array('type'=>'integer'),
        ));
      $this->createTable('ftplog_arch',array(
             'id'=>array('type'=>'integer'),
             'ftp_id'=>array('type'=>'integer'),
             'rozkaz'=>array('type'=>'text'),
             'wynik'=>array('type'=>'text'),
             'nczas'=>array('type'=>'integer'),
        ));
      $this->createTable('groups',array(
             'id'=>array('type'=>'integer','autoincrement' => true,'notnull' => true, 'primary'=>true),
             'groupname'=>array('type'=>'varchar(32)'),
        ));
      $this->createTable('login',array(
             'id'=>array('type'=>'integer','autoincrement' => true,'notnull' => true, 'primary'=>true),
             'tin'=>array('type'=>'integer'),
             'tout'=>array('type'=>'integer'),
             'server'=>array('type'=>'integer'),
             'username'=>array('type'=>'varchar(32)'),
             'ip'=>array('type'=>'character(15)'),
             'groupid'=>array('type'=>'integer'),
        ));
      $this->createTable('login_arch',array(
             'id'=>array('type'=>'integer'),
             'tin'=>array('type'=>'integer'),
             'tout'=>array('type'=>'integer'),
             'server'=>array('type'=>'integer'),
             'username'=>array('type'=>'varchar(32)'),
             'ip'=>array('type'=>'character(16)'),
             'groupid'=>array('type'=>'integer'),
        ));
      $this->createTable('passwd',array(
             'username'=>array('type'=>'varchar(32)'),
             'password'=>array('type'=>'varchar(64)'),
             'groupid'=>array('type'=>'integer'),
             'admin'=>array('type'=>$int2),
             'fullname'=>array('type'=>'text'),
             'total_time'=>array('type'=>'integer'),
             'limit_time'=>array('type'=>'integer'),
             'email'=>array('type'=>'text'),
             'skin'=>array('type'=>'varchar(64)'),
             'nlicense_agreement_date'=>array('type'=>'integer'),
             'ulang'=>array('type'=>'character(2)'),
        ));
      $this->createTable('rights',array(
             'username'=>array('type'=>'varchar(32)'),
             'server'=>array('type'=>'integer'),
             'pages'=>array('type'=>'text'),
             'menus'=>array('type'=>'text'),
             'ftp'=>array('type'=>$int2),
             'class'=>array('type'=>$int2),
             'basic'=>array('type'=>$int2),
             'proof'=>array('type'=>'text'),
             'acl'=>array('type'=>$int2),
             'nexpire'=>array('type'=>'integer'),
             'accesslevel'=>array('type'=>$int2),
             'template'=>array('type'=>$int2),
        ));
      $this->createTable('servers',array(
             'id'=>array('type'=>'integer','autoincrement' => true,'notnull' => true, 'primary'=>true),
             'nazwa'=>array('type'=>'varchar(128)'),
             'ftp_pass'=>array('type'=>'varchar(64)'),
             'ftp_user'=>array('type'=>'text'),
             'szablon'=>array('type'=>'character(32)'),
             'ver'=>array('type'=>$int2),
             'header'=>array('type'=>'integer'),
             'footer'=>array('type'=>'integer'),
             'editbordercolor'=>array('type'=>'character(7)'),
             'ftp_dir'=>array('type'=>'text'),
             'file_ext'=>array('type'=>'character(10)'),
             'groupid'=>array('type'=>'integer'),
             'ftp_server'=>array('type'=>'varchar(128)'),
             'svn'=>array('type'=>'text'),
             'versions'=>array('type'=>$int2),
             'http_url'=>array('type'=>'text'),
             'trans'=>array('type'=>'text'),
             'lang'=>array('type'=>'character(2)'),
             'project_domain'=>array('type'=>'text'),
             'nazwa_long'=>array('type'=>'varchar'),
             'nd_trash'=>array('type'=>'integer'),
             'creator'=>array('type'=>'character(32)'),
             'd_xml'=>array('type'=>'text'),
        ));
      $this->createTable('webfav',array(
             'wf_sid'=>array('type'=>'integer','autoincrement' => true, 'primary'=>true),
             'wf_user'=>array('type'=>'varchar(32)'),
             'wf_server'=>array('type'=>'integer'),
             'wf_page_id'=>array('type'=>'integer'),
             'wf_lang'=>array('type'=>'character(2)'),
        ));
      $this->createTable('webfile',array(
             'wf_id'=>array('type'=>'integer','autoincrement' => true,'notnull' => true, 'primary'=>true),
             'wf_server'=>array('type'=>'integer'),
             'wf_ver'=>array('type'=>$int2),
             'wf_gal'=>array('type'=>$int2),
             'wf_accesslevel'=>array('type'=>$int2),
             'wf_file'=>array('type'=>'varchar(100)'),
             'wf_autor'=>array('type'=>'varchar(32)'),
             'wf_d_create'=>array('type'=>'integer'),
             'wf_status'=>array('type'=>'character(1)'),
             'wf_type'=>array('type'=>'character(1)'),
             'wf_page'=>array('type'=>'integer'),
             'wf_size'=>array('type'=>'double'),
        ));
      $this->createTable('weblink',array(
             'sid'=>array('type'=>'integer','notnull' => true,'autoincrement' => true, 'primary'=>true),
             'page_id'=>array('type'=>'integer'),
             'menu_id'=>array('type'=>'integer'),
             'ver'=>array('type'=>'double'),
             'page_target'=>array('type'=>'integer'),
             'pri'=>array('type'=>$int2),
             'fgcolor'=>array('type'=>'character(6)'),
             'type'=>array('type'=>$int2),
             'class'=>array('type'=>'varchar(50)'),
             'variables'=>array('type'=>'text'),
             'server'=>array('type'=>'integer'),
             'alt'=>array('type'=>'text'),
             'name'=>array('type'=>'character(32)'),
             'hidden'=>array('type'=>$int2),
             'target'=>array('type'=>'text'),
             'submenu_id'=>array('type'=>'integer'),
             'href'=>array('type'=>'text'),
             'alt_title'=>array('type'=>'text'),
             'accesslevel'=>array('type'=>$int2),
             'lang'=>array('type'=>'character(2)'),
             'lang_target'=>array('type'=>'character(2)'),
             'ufile_target'=>array('type'=>'text'),
             'nd_create'=>array('type'=>'integer'),
             'nd_update'=>array('type'=>'integer'),
             'description'=>array('type'=>'text'),
             'd_xml'=>array('type'=>'text'),
             'img'=>array('type'=>'text'),
             'imga'=>array('type'=>'text'),
        ));
      $this->createTable('webpage',array(
             'sid'=>array('type'=>'integer','notnull' => true,'autoincrement' => true, 'primary'=>true),            
             'id'=>array('type'=>'integer'),
             'ver'=>array('type'=>'double'),
             'description'=>array('type'=>'text'),
             'keywords'=>array('type'=>'text'),
             'bgcolor'=>array('type'=>'character(6)'),
             'fgcolor'=>array('type'=>'character(6)'),
             'tbgcolor'=>array('type'=>'character(6)'),
             'tfgcolor'=>array('type'=>'character(6)'),
             'class'=>array('type'=>'character(12)'),
             'background'=>array('type'=>'varchar(80)'),
             'type'=>array('type'=>$int2),
             'next'=>array('type'=>'integer'),
             'prev'=>array('type'=>'integer'),
             'submenu_id'=>array('type'=>'integer'),
             'menu_id'=>array('type'=>'integer'),
             'server'=>array('type'=>'integer'),
             'title'=>array('type'=>'text'),
             'hidden'=>array('type'=>$int2),
             'tree'=>array('type'=>'text'),
             'pagekey'=>array('type'=>'text'),
             'nositemap'=>array('type'=>$int2),
             'noproof'=>array('type'=>$int2),
             'title_short'=>array('type'=>'varchar(64)'),
             'nd_create'=>array('type'=>'integer'),
             'nd_update'=>array('type'=>'integer'),
             'nd_ftp'=>array('type'=>'integer'),
             'proof_autor'=>array('type'=>'varchar(32)'),
             'proof_date'=>array('type'=>'integer'),
             'unproof_autor'=>array('type'=>'varchar(32)'),
             'unproof_date'=>array('type'=>'integer'),
             'unproof_counter'=>array('type'=>'integer'),
             'accesslevel'=>array('type'=>$int2),
             'lang'=>array('type'=>'character(2)'),
             'unproof_sids'=>array('type'=>'text'),
             'unproof_comment'=>array('type'=>'text'),
             'default_file_name'=>array('type'=>'text'),
             'd_xml'=>array('type'=>'text'),
             'file_name'=>array('type'=>'text'),
             'langs_related'=>array('type'=>'text'),
        ));
      $this->createTable('webtd',array(
             'sid'=>array('type'=>'integer','notnull' => true,'autoincrement' => true, 'primary'=>true),        
             'page_id'=>array('type'=>'integer'),
             'ver'=>array('type'=>'double'),
             'pri'=>array('type'=>$int2),
             'img'=>array('type'=>'varchar(50)'),
             'plain'=>array('type'=>'text'),
             'html'=>array('type'=>'varchar(50)'),
             'menu_id'=>array('type'=>'integer'),
             'class'=>array('type'=>'varchar(50)'),
             'align'=>array('type'=>'character(12)'),
             'valign'=>array('type'=>'character(12)'),
             'bgcolor'=>array('type'=>'character(6)'),
             'cos'=>array('type'=>$int2),
             'width'=>array('type'=>'character(10)'),
             'type'=>array('type'=>$int2),
             'level'=>array('type'=>$int2),
             'title'=>array('type'=>'text'),
             'more'=>array('type'=>'integer'),
             'next'=>array('type'=>'integer'),
             'size'=>array('type'=>'integer'),
             'bgimg'=>array('type'=>'text'),
             'server'=>array('type'=>'integer'),
             'api'=>array('type'=>'varchar(50)'),
             'costxt'=>array('type'=>'text'),
             'hidden'=>array('type'=>$int2),
             'staticinclude'=>array('type'=>$int2),
             'autor'=>array('type'=>'text'),
             'autor_update'=>array('type'=>'text'),
             'xml'=>array('type'=>'text'),
             'nd_create'=>array('type'=>'integer'),
             'nd_update'=>array('type'=>'integer'),
             'nd_valid_from'=>array('type'=>'integer'),
             'nd_valid_to'=>array('type'=>'integer'),
             'swfstyle'=>array('type'=>$int2),
             'ob'=>array('type'=>$int2),
             'accesslevel'=>array('type'=>$int2),
             'uniqueid'=>array('type'=>'character(8)'),
             'lang'=>array('type'=>'character(2)'),
             'nohtml'=>array('type'=>'text'),
             'd_xml'=>array('type'=>'text'),
             'web20'=>array('type'=>'text'),
             'js'=>array('type'=>'text'),
             'widget'=>array('type'=>'varchar(60)'),
             'widget_data'=>array('type'=>'text'),
        ));
      $this->createTable('webver',array(
             'wv_id'=>array('type'=>'integer','autoincrement' => true,'notnull' => true, 'primary'=>true),
             'wv_date'=>array('type'=>'integer'),
             'wv_date_ftp'=>array('type'=>'integer'),
             'wv_autor'=>array('type'=>'character(32)'),
             'wv_autor_ftp'=>array('type'=>'character(32)'),
             'wv_action'=>array('type'=>'varchar(48)'),
             'wv_table'=>array('type'=>'character(32)'),
             'wv_sid'=>array('type'=>'integer'),
             'wv_query'=>array('type'=>'text'),
             'wv_webver'=>array('type'=>'text'),
             'wv_uwagi'=>array('type'=>'text'),
             'wv_noproof'=>array('type'=>$int2),
        ));
      $this->addIndex( 'ftp', 'ftp_all_key', array('fields'=>array('server','t_begin','t_end')) );
      $this->addIndex( 'ftp', 'ftp_id_key', array('fields'=>array('id')) );
      $this->addIndex( 'ftplog', 'ftplog_ftp_id_key', array('fields'=>array('ftp_id')) );
      $this->addIndex( 'ftplog', 'ftplog_id_key', array('unique'=>true,'fields'=>array('id')) );
      $this->addIndex( 'groups', 'group_id_key', array('unique'=>true,'fields'=>array('id')) );
      $this->addIndex( 'login_arch', 'login_arch_id_key', array('fields'=>array('id')) );
      $this->addIndex( 'login_arch', 'login_arch_tin_key', array('fields'=>array('tin')) );
      $this->addIndex( 'login_arch', 'login_arch_tout_key', array('fields'=>array('tout')) );
      $this->addIndex( 'login_arch', 'login_arch_username_key', array('fields'=>array('username')) );
      $this->addIndex( 'login', 'login_id_key', array('unique'=>true,'fields'=>array('id')) );
      $this->addIndex( 'login', 'login_tin_key', array('fields'=>array('tin')) );
      $this->addIndex( 'login', 'login_tout_key', array('fields'=>array('tout')) );
      $this->addIndex( 'login', 'login_username_key', array('fields'=>array('username')) );
      $this->addIndex( 'passwd', 'passwd_username_key', array('fields'=>array('username')) );
      $this->addIndex( 'rights', 'rights_server_key', array('fields'=>array('server')) );
      $this->addIndex( 'rights', 'rights_username_key', array('fields'=>array('username')) );
      $this->addIndex( 'servers', 'servers_creator_key', array('fields'=>array('creator')) );
      $this->addIndex( 'servers', 'servers_id_key', array('unique'=>true,'fields'=>array('id')) );
      $this->addIndex( 'servers', 'servers_nazwa_key', array('fields'=>array('nazwa')) );
      $this->addIndex( 'webfav', 'webfav_all_key', array('unique'=>true,'fields'=>array('wf_user','wf_server','wf_page_id','wf_lang')) );
      $this->addIndex( 'webfile', 'webfile_autor_key', array('fields'=>array('wf_autor')) );
      $this->addIndex( 'webfile', 'webfile_file_key', array('fields'=>array('wf_file')) );
      $this->addIndex( 'webfile', 'webfile_key', array('fields'=>array('wf_server','wf_ver','wf_gal')) );
      $this->addIndex( 'webfile', 'webfile_wf_id_key', array('unique'=>true,'fields'=>array('wf_id')) );
      $this->addIndex( 'weblink', 'weblink_all2_key', array('fields'=>array('server','ver','lang','menu_id','page_target')) );
      $this->addIndex( 'weblink', 'weblink_all_key', array('fields'=>array('menu_id','ver','server','lang','pri')) );
      $this->addIndex( 'weblink', 'weblink_lang_key', array('fields'=>array('lang')) );
      $this->addIndex( 'weblink', 'weblink_menu_key', array('fields'=>array('menu_id')) );
      $this->addIndex( 'weblink', 'weblink_page_key', array('fields'=>array('page_id')) );
      $this->addIndex( 'weblink', 'weblink_pri_key', array('fields'=>array('pri')) );
      $this->addIndex( 'weblink', 'weblink_server_key', array('fields'=>array('server')) );
      $this->addIndex( 'weblink', 'weblink_sid_key', array('unique'=>true,'fields'=>array('sid')) );
      $this->addIndex( 'webtd', 'weblink_unique_all_key', array('unique'=>true,'fields'=>array('server','menu_id','sid')) );
      $this->addIndex( 'weblink', 'weblink_ver_key', array('fields'=>array('ver')) );
      $this->addIndex( 'webpage', 'webpage_all_key', array('fields'=>array('id','ver','server','lang')) );
      $this->addIndex( 'webpage', 'webpage_id_key', array('fields'=>array('id')) );
      $this->addIndex( 'webpage', 'webpage_lang_key', array('fields'=>array('lang')) );
      $this->addIndex( 'webpage', 'webpage_nd_update_key', array('fields'=>array('nd_update')) );
      $this->addIndex( 'webpage', 'webpage_prev_key', array('fields'=>array('prev')) );
      $this->addIndex( 'webpage', 'webpage_server_key', array('fields'=>array('server')) );
      $this->addIndex( 'webpage', 'webpage_sid_key', array('unique'=>true,'fields'=>array('sid')) );
      $this->addIndex( 'webpage', 'webpage_unique_all_key', array('unique'=>true,'fields'=>array('server','id','prev','sid')) );
      $this->addIndex( 'webpage', 'webpage_ver_key', array('fields'=>array('ver')) );
      $this->addIndex( 'webtd', 'webtd_all2_key', array('fields'=>array('server','ver','lang','page_id','menu_id','next','more','level')) );
      $this->addIndex( 'webtd', 'webtd_all_key', array('unique'=>true,'fields'=>array('page_id','ver','server','lang','pri','level','sid')) );
      $this->addIndex( 'webtd', 'webtd_lang_key', array('fields'=>array('lang')) );
      $this->addIndex( 'webtd', 'webtd_level_key', array('fields'=>array('level')) );
      $this->addIndex( 'webtd', 'webtd_menu_key', array('fields'=>array('menu_id')) );
      $this->addIndex( 'webtd', 'webtd_page_key', array('fields'=>array('page_id')) );
      $this->addIndex( 'webtd', 'webtd_pri_key', array('fields'=>array('pri')) );
      $this->addIndex( 'webtd', 'webtd_server_key', array('fields'=>array('server')) );
      $this->addIndex( 'webtd', 'webtd_sid_key', array('unique'=>true,'fields'=>array('sid')) );
      $this->addIndex( 'webtd', 'webtd_type_key', array('fields'=>array('type')) );
      $this->addIndex( 'webtd', 'webtd_unique_all_key', array('unique'=>true,'fields'=>array('server','page_id','level','sid')) );
      $this->addIndex( 'webtd', 'webtd_uniqueid_key', array('fields'=>array('uniqueid')) );
      $this->addIndex( 'webtd', 'webtd_valid_from_key', array('fields'=>array('nd_valid_from')) );
      $this->addIndex( 'webtd', 'webtd_valid_to_key', array('fields'=>array('nd_valid_to')) );
      $this->addIndex( 'webtd', 'webtd_ver_key', array('fields'=>array('ver')) );
      $this->addIndex( 'webver', 'webver_sid_key', array('fields'=>array('wv_sid')) );
      $this->addIndex( 'webver', 'webver_wv_id_key', array('unique'=>true,'fields'=>array('wv_id')) );
    
    
    }

    protected function newKameleonDown() {
      $this->dropTable('ftp');
      $this->dropTable('ftp_arch');
      $this->dropTable('ftplog');
      $this->dropTable('ftplog_arch');
      $this->dropTable('groups');
      $this->dropTable('login');
      $this->dropTable('login_arch');
      $this->dropTable('passwd');
      $this->dropTable('rights');
      $this->dropTable('servers');
      $this->dropTable('webfav');
      $this->dropTable('webfile');
      $this->dropTable('weblink');
      $this->dropTable('webpage');
      $this->dropTable('webtd');
      $this->dropTable('webver');
    }



    
    protected function login_all_view($drop=false) {
        if ($drop) $sql="DROP VIEW login_all";
        else $sql="CREATE VIEW login_all AS
                    SELECT login_arch.id, login_arch.tin, login_arch.tout, login_arch.server, login_arch.username, login_arch.ip, login_arch.groupid
                    FROM login_arch
                    UNION
                    SELECT login.id, login.tin, login.tout, login.server, login.username, login.ip, login.groupid
                    FROM login;";
        $this->conn->execute($sql);
    }
    
    
    protected function oldKameleonUp() {
        $tabs = $this->conn->fetchAll("SELECT * FROM pg_tables WHERE schemaname='public'");
        
        $this->login_all_view(true);
        foreach ($tabs AS $tab) {
            

            $sql="select column_name,data_type,character_maximum_length from INFORMATION_SCHEMA.COLUMNS where table_name = '".$tab['tablename']."'";
            $cols=$this->conn->fetchAll($sql);
            
            foreach($cols AS $col) {

                if ((strstr($col['column_name'],'autor') || strstr($col['column_name'],'user'))  && $col['character_maximum_length']==16) {
                    $sql="ALTER TABLE ".$tab['tablename']." ALTER COLUMN ".$col['column_name']." TYPE varchar(32)";
                    $this->conn->execute($sql);
                }
                
                if ($col['character_maximum_length']>=16 && !strstr($col['data_type'],'vary')) {
                    $sql="ALTER TABLE ".$tab['tablename']." ALTER COLUMN ".$col['column_name']." TYPE varchar(".$col['character_maximum_length'].")";
                    $this->conn->execute($sql);
                }
            }
            
        }
        $this->login_all_view();
        $this->reindex();
    }
    
    protected function oldKameleonDown() {
        $tabs = $this->conn->fetchAll("SELECT * FROM pg_tables WHERE schemaname='public'");
        
        $this->login_all_view(true);
        foreach ($tabs AS $tab) {
            

            $sql="select column_name,data_type,character_maximum_length from INFORMATION_SCHEMA.COLUMNS where table_name = '".$tab['tablename']."'";
            $cols=$this->conn->fetchAll($sql);
            
            foreach($cols AS $col) {

                if ( (strstr($col['column_name'],'autor') || strstr($col['column_name'],'user')) && $col['character_maximum_length']==32) {
                    $sql="ALTER TABLE ".$tab['tablename']." ALTER COLUMN ".$col['column_name']." TYPE char(16)";
                    $this->conn->execute($sql);
                }
                

                if ($col['character_maximum_length']>=16 && strstr($col['data_type'],'vary') && substr($tab['tablename'],0,3)!='pl_') {
                    $sql="ALTER TABLE ".$tab['tablename']." ALTER COLUMN ".$col['column_name']." TYPE char(".$col['character_maximum_length'].")";
                    $this->conn->execute($sql);
                }                
                
            }
            
        }
        $this->login_all_view();
        $this->reindex();
    }
    
    protected function reindex()
    {
        $sql="SELECT indexrelname FROM pg_stat_all_indexes WHERE schemaname = 'public'";
        $indexes = $this->conn->fetchAll($sql);
        
        foreach ($indexes AS $index) {
            $sql="REINDEX INDEX ".$index['indexrelname'];
            $this->conn->execute($sql);
        }
    }
    
}

