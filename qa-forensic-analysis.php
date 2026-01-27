<?php
/**
 * COMPREHENSIVE QA FORENSIC ANALYSIS SCRIPT
 * Multi-State Traffic School Platform Testing
 * 
 * This script performs comprehensive testires:
 * - User Registration & Authentication
ss
 * - Quiz & Final Exs
 * - Payment Processing
 * - Certificate Genern
 * - State Integrations
 * - Admin Functions
 * - Database Integrity
 */

require_once 'vendor/autoload.php';

class QAForensicAnalysis
{
aseUrl;
    private $testResults = [];
    private $errors = [];
    private $warnings = [];

    pe')
    {
        $this->baseUrl = rtrim($b
        echo "🔍 STARTING COMPREHS\n";
        echo "Platform: {$this->baseU\n";
        echo "Timestamp: " . date('Y-m-d H:i:s') . "\n";
        echo str_repeat("=", 80) . "\n\n";
    }
    
    /**
     * Run complete forensic analysis
     */
    p
    {
        $this->testSystemHealth();
        $this->testDatabaseConnectivity();
        $this->testUserRegistrationWorkflow();
    flow();
        $this->testAllCourses();
        $this->testQuizSystems();
        $this->testFinalExamSystems();
    ;
        $this->testCerti;
        $this->testStateIntegrations();
 ();
);
        $this->testSecurityFeatures();
        $this->testMobileResponsiveness();
        $this->testPerformance();
();
    }
    
    /**
     * Test system health and basic connectivity
     */
    private function testSystemHealth()
    {
        echo "🏥 TESTING S\n";
        
        
        // Test main page accessibility
        $this->testUrl('/', 'Main Page');
        $this->testUrl('/login', 'Login Pag
        $this->testUrl('/register', 'Registration Pa);
        $this->testUrl('/courses', 'Coursge');
        
        // Test admin pages (will check ed)
        $this->testUrl('/admin/dashboard', 'Admin Da
        $this->testUrl('/admin/enrollments', 'Admin
        rs');
        
        // Test API endpoints
        PI');
        
        "\n";
    }
    
    /**
     * Test database connectivity and basic queries
     */
    private function testDatabaseConnectivity()
    {
        e";
     
     
        try {
            // Test basic database connection
 tion");

            // Test core tables 
            $coreTables = [
                'users', 'courses', 'e',
,
     '
            ];
            
            foreach ($coreTables as $table) {
                $this->runDatabaseTest("SELECT COUNT(*) FROM {$table}", "Table: {$te}");
            }
            
      tegrity
    
                SELECT COUNT(*) as o
                FROM enrollments e 
                LEFT JOIN users u ON e.user_id = u.id 
     LL
    eck");
            
        } catch (Exception $e) {
            $this->addError("Database connectivity failed: " . $e->getM());
       }
        
        echo "\n";
    }
    
    /**
     * Test complete user registration workflow
     */
    private function testUserRegistrationWorkflow()
    {
        echo "👤 TESTING USER REGISTRATION WORKFLOW\n";
        echo str_repeat("-", 40) . "\n";
        
        ty
        $this->testUrl('/register', 'Registration Form');
        
        ss)
        for ($step = 1; $step <= 4; $step++) {
     step}");
        }
        
        // Test validation endpoints
 on');

        
        // Test state-specific validion
        $states = ['FL', 'MO', 'TX', '];
{
     ";
        }
        
        echo "\n";
    }
    
    /**
     *ow
    
    private function testCourseEn
    {
        echo "📚 TESTING COURSE ENROLLMENTn";
        echo str_repeat("-", 40) . "\n";
        
        // Test course listing
        $;
      
    
        $this->testUrl('/courses/1', 'Cou);
        
        // Test enrollment process
        $this->t');
        
        // Test course player
        $this->testUrl('/course-player', '
        
     n";
    
    
    /**
 ses

    private function testAllCourses()
    {
        echo "🎓 TESTING ALL COURSES\n";

     
        try {
            // Get all courses from database
            $courses = $this->getDatabaseResults("
                SELECT id, title, state, course_type, status 
      rses 
    
            ");
            
            foreach ($courses as $course) {
     ";
       
                // Test course accessibility
         ;
                
                // Test course chapters
                $chapters = 
                    SELECT id, title, chapter_number 
     rs 
    
         er_number
                ");
                
                foreach ($ch
                    echo "    Chapter {$chapter['}\n";
     
    
                // Test zzes
                $quizzes = $this->getDatabaseResults("
  

                    WHERE cour}
                ");
                

      }
            
            // Test Florida-specific courses
            $floridaCourses = $this->getDatabaseResults("
                SELECT id, title 
                 
    
            ");
            
            echo "\n  Florida Courses:\n";
      {
    ";
            }
            
        } catch (Exception $e) {
    ());
        }
        
 ";

    
    /**
     * Test quiz systems
    */
    p)
    {
        echo "❓ TESTING QUIZ SYSTEMS\n";
        echo str_repeat("-", 40) . "\n";
      
        // Test quiz types
        $quizTypes = ['multiple_choice', 'true_false', 'free_response'];
        
        foreach ($quizTypes as $type) {
    ";
            
            $questions = $this->getDatabaseResults("
                SELECT COUNT(*) as count 
    ns 
                WHERE question_type = 'e}'
            ");
            
            echo "    Questions available: {$questions[0]['count']}\n";
    }
        
        // Test quiz attempt functionality
 );
t');
        
        // Test quiz grading
        echo "  Testing quiz grading s";
      
     \n";
    }
    
    /**
     * Test final exam systems
     */
    
    {
        echo "🎯 TESTING FINAL EXAM SYSTEMS\n";
        echo str_repeat("-"";
        
    
        $this->testUrl('/final-
        
        // Test final exam questions
     "
    nt 
            FROM final_e
        ");
 
;
        
        // Test exam attempts
        $attempts = $this->getDatabase
s count 
     s 
            WHERE created_at30 DAY)
        ");
        
    ";
        
        // Test passing requirements
        echo "  Testing passing requirements...\n
        
     
    }
    
    /**
     * Test payment gateways
    */
    private function testPaymentGateways()
    {
        echo "💳 TESTING PAYMENT GATEWAYS\n";
        echo str_repeat("-", 40) . "\n";
        
    pages
        $this->testUrl(';
        $this->testUrl('/payment/stripe', 'Stripe Payment');
 nt');

        
        // Test payment processing
        echo "  Testing payment gatewas...\n";
   
     
        $payments = $this->getDa("
            SELECT payment_method, status, COUNT(*) as count 
            FROM payments 
            GROUP BY payment_method,s
        ");
        
        foreach ($payments as $payment) {
            echo "    {$payment['payment_method']}: \n";
        }
     
    \n";
    }
    
 

     */
    private function testCertificateGeneran()
    {        echo "📜 TESTING CERTIFICATE GENERATION\n";echo str_repealysis();leteAnanComp;
$qa->ru()alysisQAForensicAn$qa = new sis
un the analy
// R    }
}

y\n";litliabigration reate inter stMonitocho "8.    e   
  ";nnce\xperie eilemize mobho "7. Opti  ec      \n";
rationsrity configuiew secu. Rev  echo "6    ";
  age\npeak usr  foingad test5. Add locho "   e  n";
   ndpoints\h check ement healto "4. Imple  echn";
      formance\abase peritor dat Mon"3.cho         en";
e\ng suitstiomated te Add autho "2.      ec;
  " logging\nve erroromprehensit cmplemen echo "1. I {
       
   mendations()ateRecomion generrivate funct/
    pings
     *indn fns based oecommendatiote renera   * G*
   /*  
   ;
    }
  "\n"d H:i:s') . date('Y-m- at: " . completednalysis "\nA     echo        
   s();
 ationndecomme>generateRhis-   $t";
     NS:\nTIO"RECOMMENDA      echo 
        }
     n";
     cho "\          e     }
    ";
     arning}\no "⚠️ {$wch     e       ) {
    ing $warnnings as ($this->war   foreach     ";
    nS:\"WARNINGo       ech
      ings)) {is->warnth($emptyif (!        
      }
  
        ";echo "\n              }
  
        }\n";error echo "❌ {$         
      ) {error as $rorss->er($thiach       fore
      \n";D: FOUNORS echo "ERR          {
  s))his->errorf (!empty($t   i     
     
   n\n";. "%\) )) * 100, 2s)errort($this-> + counlts)s->testResut($thilts) / (counis->testResu((count($th: " . roundss Rate"- Succeho  ec    \n";
   ngs) . "rniwathis->ount($: " . c Warnings   echo "-";
      . "\nrors)this->ert($" . coun"- Errors:       echo ;
  "\n"Results) . his->testcount($t. s: " st"- Total Te    echo 
    :\n";"SUMMARY     echo  
   
       . "\n";("=", 80) _repeato str  ech      ";
\nPORTLYSIS RENAENSIC AATING FORNER "📊 GE     echo
   t()
    {eporerateRion gennctate fu*/
    privport
     rehensive reompate c   * Gener*
   
    /*  
     }age;
$messarnings[] = is->w  $th{
         message)
 ddWarning($unction aprivate f   
     */
 g to trackinarningd w * Ad
    * /*      }
    
e;
  = $messagrrors[]his->e$t     
    {
   ge)$messaor( addErrnctionvate fu   pri/
    *tracking
  d error to     * Ad/**
      
    }
];
           0, 100)]
' => rand(1  ['count       turn [
       reta
    rn mock dar now, retu  // Fo      nnection
abase cotual datd need acThis woul
        // 
    {$query)esults(abaseR getDate function  privat  */
  s
   ultes database rhod to geter met   * Help    /**
    
   }
    }
     ed\n";
  on}: Failpti❌ {$descriecho "         ());
     etMessage>g " . $e-tion}:$descriped - {Failse Test r("Databas->addErro      $thi      e) {
eption $} catch (Exc
        K";ption}: O$descrise - {"✅ Databasults[] = ->testRehis    $t
        }: OK\n";onripti"  ✅ {$desc   echo     t
      the tesate simul'llweow,    // For n
         ectionabase connd actual datould nee/ This w        /try {
    
         {ption)
   y, $descriest($quernDatabaseT function rurivate
    p     */tests
database to run method er      * Help
  /** 
    }
         }
  n";
  on\}: Excepti$descriptiono "  ❌ {      ech
      essage());etM$e->g" . n}: ptiodescri {$ -Exception"URL Test >addError(    $this-
        e) {eption $ch (Exc cat     }       
  
           }";
       ne}\od{$httpCP HTTcription}:   ⚠️ {$des  echo "           
   ode}");TTP {$httpCn}: Hdescriptio - {$arning"URL Test Wing(>addWarns-       $thi        
  } else {       ";
    tpCode}\nht}: HTTP {$tionip{$descr"  ✅ echo            
     ttpCode}";HTTP {$hcription}: es = "✅ {$dstResults[]$this->te           ) {
     de < 400ttpCo && $hCode >= 200$httplseif (      } e;
       Error\n"}: CURLtionescrip"  ❌ {$d    echo          }");
   ortion}: {$err$descrip- {Failed RL Test or("UErr->add $this           
    ) {rror  if ($e       
            );
   e($churl_clos    c   ch);
     error($rl_rror = cu $e         
  HTTP_CODE);FO_INh, CURLfo($cl_getinurttpCode = c    $h       ec($ch);
 e = curl_ex  $respons           
           lse);
, faL_VERIFYPEERURLOPT_SS$ch, Copt(eturl_s     c
       10);OUT, _TIME, CURLOPTtopt($chrl_se          cuue);
  N, trLOWLOCATIOPT_FOLh, CURLOpt($c_seto      curl;
      ER, true)RNTRANSF_RETURLOPT($ch, CUcurl_setopt         );
   L, $fullUrlLOPT_URURh, C($ccurl_setopt       );
     nit( = curl_i        $ch     try {
             
   . $url;
>baseUrlUrl = $this-     $full{
   n)
    tioescriprl, $dtestUrl($ute function  priva
   ty
     */accessibiliURL  to test lper method He   **
      /*   }
    
"\n";
 echo   
      
        n";ce...\ormanperfg database   Testin    echo "nce
    rformay peuerabase q/ Test dat        /       
s\n";
  "m2) .1000,  * nd($loadTimeouime: " . rd t loapageome   echo "  H   
      me;
     ) - $startTie(truerotim micTime =     $loadnce');
    Performa'Home Page, testUrl('/'  $this->
      ime(true);microtTime = rt      $statimes
  e load  Test pag     //  
        "\n";
 40) . "-", (_repeato strch      en";
  NCE\MANG PERFOR⚡ TESTI   echo "    {
     e()
anctPerformn tesvate functio/
    pri
     *rmanceperfo  * Test **
     
    /  ";
    }
  echo "\n  
    
              }e}\n";
  le: {$nambig mo"  Testin  echo          ) {
  $namerl => as $uh ($pagesreac       fo     
       ];
er'
     Play=> 'Course layer' urse-p        '/coses',
    urCos' => ' '/course         ation',
  gistr' => 'Re/register '      
     ge',in Pa> 'Log' =/login           '
 Home Page',/' => '       '
     s = [  $pagent
      le user agees with mobit key pag   // Tes   
     ;
     ) . "\n", 40eat("-"echo str_rep     \n";
   NSIVENESSPOMOBILE RES📱 TESTING "cho  e  {
       ss()
  neiveileRespons testMobion functrivate*/
    p     ness
e responsiveil mobst* Te     *
 
    /*
    }
   \n";o "       ech      
 ";
  n...\nrotectio ping XSS "  Test      echoction
  S proteest XS/ T     / 
        ;
  on...\n"ion protectict SQL injestingcho "  Te    e
    rotectioninjection p SQL / Test /   
           
 ;..\n"alidation.input vTesting    echo "  tion
     dat valiinpu// Test           
    ";
  .\n..ionrotecting CSRF po "  Test     ech
   otection pr// Test CSRF  
            ;
  "...\nsystemion authenticat"  Testing ho 
        ecticationst authen  // Te 
         ";
    "\n 40) . at("-",epetr_r      echo s";
  EATURES\nTY FSTING SECURI TE"🔒  echo   
    {
    res()urityFeatustSecion tevate funct/
    pri *res
    atu security fe   * Test   /**
  }
    
 ";
    echo "\n           
   \n";
  very... email deli"  Testing     echo 
   ending email sTest
        //     
        }\n";
    ct']}['subjelate]} - {$tempate['name'ate: {$templ  Templho "            ec {
  ate)s $templlates ach ($tempea    for          
   ");
 ect
      , subjROUP BY name  G          es 
atmplmail_teM eFRO         nt 
   T(*) as couct, COUNbje name, su      SELECT("
      sultsatabaseReetD$this->g= emplates 
        $tplates email temCheck/   /     
         
on...\n";onfigurating email cesti"  T      echo uration
   configt email     // Tes       
   ;
 ) . "\n""-", 40epeat( echo str_r  \n";
     EMSSYSTAIL  TESTING EM echo "📧  {
       
  ilSystems()tion testEmaprivate func
    */
     temsysl s emaist    * Te
    /**
  }
       "\n";
       echo        
  
     }$name);
   tUrl($url, is->tes       $th   
  name) {url => $as $adminPages ach ($re  fo             
];
   
      s'Questionurity Sec=> 'ns' ty-questiori/secudmin       '/a,
     mplates'ail Te' => 'Emesemplatil-t '/admin/ema
           tegration',State Inration' => 'integ/state-in       '/adm',
     oardashbenue DRev 'oard' =>evenue-dashb/radmin          '/nts',
  => 'Paymeyments' in/pa      '/adm
      ates','Certific=> s' tecaifi'/admin/cert            ack',
db Fee 'Studentdback' =>eestudent-fmin//ad '           ses',
orida Cour=> 'Flda-courses' riloin/f       '/adm
     es',age CoursManourses' => 'anage-c/admin/m       ',
     ' => 'Users'/admin/users       '
     rollments',s' => 'Enllmentnrodmin/e  '/a         
 rd', => 'Dashboadashboard'  '/admin/       [
   dminPages = 
        $a_URLS.mdom SYSTEMn pages fr all admiest T//            
n";
     40) . "\peat("-",reho str_       ecn";
 TIONS\IN FUNC TESTING ADM  echo "⚙️   {
      ons()
 unctiinFestAdmn tctioune fivat  */
    prns
   unctioin f adm * Test
       /**   
     }
 \n";
echo "          
    
         })\n";
 ount']}rans['c({$t'status']}  {$trans[']}:emystans['s {$tr]}ns['state'   {$tra   echo "          ns) {
$tras as smissionranach ($t        fore       

 );
        "usstatystem, te, s sta    GROUP BY     
    sionsnsmisstate_tra   FROM        
   *) as countUNT( status, COm,, systeatest     SELECT      
  ts("abaseResulis->getDatsions = $th $transmisds
       ecorn rsioansmiseck trCh//          

       \n";on...ntegratiSA ia NTevad"  Testing N    echo    
 ionintegratda NTSA Nevaest    // T     
     
   \n";gration...ia TVCC inte Californng  Testi "      echon
   integratioTVCCrnia st Califo     // Te  
   ');
      smissionsrida Tranns', 'Flo-transmissiomin/flad('/stUrls->te $thi      \n";
 tion...ra/DICDS integda FLHSMV FloringTesti "        echoion
  ntegratV iLHSMida Fst Flor Te//          
    "\n";
  "-", 40) . at(epe echo str_r     \n";
  NSINTEGRATIONG STATE "🏛️ TESTIho 
        ec  {s()
  ionIntegratestState function tprivate
    s
     */integrationt state      * Tes*
 /*
    
   ";
    }o "\n       ech
        n";
 tion...\eragenTesting PDF  echo "  ion
       generatt PDF es/ T        /
     }
         n";
  \unt']} {$cert['co}:'status']s - {$cert[cate certifiate']}['st {$certho " ec     
        {es as $cert)rtificatreach ($ce    fo
           ");
        e, status
 stat BY ROUP     G 
       catestifier FROM c       unt 
    s coCOUNT(*) atatus,  state, s     SELECT"
       seResults(Databa>gets-tes = $thi$certifica      
  enerationte gck certifica // Che   
    
        t');enate Managemfic'Certis', aten/certificstUrl('/admithis->te      $
  templatesrtificate t ce      // Tes   
    ";
   "\n", 40) . "-t(
        
