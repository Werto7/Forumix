<?php
class CaptchaBox
{
    private $next_text = "Next";
    private $rotate_text = "Rotate";
    private $reset_text = "Reset";
    private $noRobot_text = "I'm not a robot";
    
    private string $path;

    public function __construct($name = 'captcha')
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        $this->path = dirname(__DIR__);
    }

    public function showCaptchaInline()
    {
    	$_SESSION['captcha_start'] = time();
        $_SESSION['captcha_timeout'] = 60; //Seconds

        $jsonPath = $this->path . '/spinCaptcha_pictures/answers.json';
        $entries = json_decode(file_get_contents($jsonPath), true);

        if (!is_array($entries) || count($entries) < 3) {
            echo '<p style="color:red;">Not enough CAPTCHA data available</p>';
            return;
        }

        shuffle($entries);
        $selected = array_slice($entries, 0, 3);

        //Preparation of the results
        $base64_originals = [];
        $base64_rotated = [];

        foreach ($selected as $index => $entry) {
            $name = $entry['name'];
            $rotation = $entry['rotation'];

            $_SESSION['captcha_rotation_' . $index] = $rotation;

            $orig_path = $this->path . '/spinCaptcha_pictures/originals/' . $name;
            $rotated_path = $this->path . '/spinCaptcha_pictures/rotated/' . $name;

            $base64_originals[$index] = file_exists($orig_path) ? trim(file_get_contents($orig_path)) : '';
            $base64_rotated[$index] = file_exists($rotated_path) ? trim(file_get_contents($rotated_path)) : '';
        }
        $img1_o = htmlspecialchars($base64_originals[0]);
        $img1_r = htmlspecialchars($base64_rotated[0]);
        $img2_o = htmlspecialchars($base64_originals[1]);
        $img2_r = htmlspecialchars($base64_rotated[1]);
        $img3_o = htmlspecialchars($base64_originals[2]);
        $img3_r = htmlspecialchars($base64_rotated[2]);

        echo '<style>
            .captcha-container {
                position: relative;
                display: inline-block;
            }

            .captcha-checkbox {
                appearance: none;
                -webkit-appearance: none;
                width: 30px;
                height: 30px;
                border: 2px solid #333;
                border-radius: 4px;
                background-color: #fff;
                cursor: pointer;
                position: relative;
                margin-right: 8px;
            }

            #img2:not(:checked) ~ #captchaCheck:checked::after {
                content: "";
                position: absolute;
                top: 2px;
                left: 2px;
                width: 18px;
                height: 18px;
                border: 2px solid #999;
                border-top-color: transparent;
                border-radius: 50%;
                animation: spin 0.8s linear infinite;
            }
        
            #img2:checked ~ #captchaCheck::after {
                content: "\2713";
                color: green;
                font-size: 24px;
                position: absolute;
                top: -2px; left: 4px;
                animation: fadein 0.3s ease-in;
            }

            @keyframes spin { to { transform: rotate(360deg); } }
            @keyframes fadein {
                from { opacity: 0; transform: scale(0.5); }
                to   { opacity: 1; transform: scale(1); }
            }

            .captcha-frame {
                position: absolute;
                bottom: 100%;
                left: 0;
                transform: translateY(-10px);
                width: 220px;
                height: 260px;
                z-index: 999;
                background: white;
                border: 1px solid #aaa;
                box-shadow: 2px 2px 6px rgba(0,0,0,0.2);
                padding: 5px;
                display: none;
            }

            #captchaCheck:checked ~ .captcha-frame {
                display: block;
            }

            .container {
                display: none;
                flex-direction: column;
                align-items: center;
            }

            .outer-circle {
                margin-top: 5px;
                width: 140px;
                height: 140px;
                border-radius: 50%;
                overflow: hidden;
                position: relative;
                display: flex;
                justify-content: center;
                align-items: center;
                background-color: #ccc;
            }

            .outer-circle img.outer,
            .outer-circle img.inner-image {
                width: 100%;
                height: 100%;
                object-fit: cover;
                position: absolute;
                top: 0;
                left: 0;
                border-radius: 50%;
                transition: transform 0.3s ease;
            }

            .inner-image {
                mask-image: radial-gradient(circle at center, white 0%, white 59%, transparent 60%);
                -webkit-mask-image: radial-gradient(circle at center, white 0%, white 59%, transparent 60%);
                box-shadow: 0 0 5px rgba(0,0,0,0.3);
            }

            .circle {
                position: absolute;
                width: 84%;
                height: 84%;
                border: 3px solid black;
                border-radius: 50%;
                background-color: transparent;
                pointer-events: none;
            }

            input[type="radio"] {
                display: none;
            }

            #r0:checked ~ .outer-circle img.outer { transform: rotate(0deg); }
            #r1:checked ~ .outer-circle img.outer { transform: rotate(70deg); }
            #r2:checked ~ .outer-circle img.outer { transform: rotate(140deg); }
            #r3:checked ~ .outer-circle img.outer { transform: rotate(210deg); }
            #r4:checked ~ .outer-circle img.outer { transform: rotate(280deg); }
            #r5:checked ~ .outer-circle img.outer { transform: rotate(350deg); }
            #r6:checked ~ .outer-circle img.outer { transform: rotate(420deg); }
            #r7:checked ~ .outer-circle img.outer { transform: rotate(490deg); }
    
            #r8:checked ~ .outer-circle img.outer { transform: rotate(0deg); }
            #r9:checked ~ .outer-circle img.outer { transform: rotate(70deg); }
            #r10:checked ~ .outer-circle img.outer { transform: rotate(140deg); }
            #r11:checked ~ .outer-circle img.outer { transform: rotate(210deg); }
            #r12:checked ~ .outer-circle img.outer { transform: rotate(280deg); }
            #r13:checked ~ .outer-circle img.outer { transform: rotate(350deg); }
            #r14:checked ~ .outer-circle img.outer { transform: rotate(420deg); }
            #r15:checked ~ .outer-circle img.outer { transform: rotate(490deg); }
    
            #r16:checked ~ .outer-circle img.outer { transform: rotate(0deg); }
            #r17:checked ~ .outer-circle img.outer { transform: rotate(70deg); }
            #r18:checked ~ .outer-circle img.outer { transform: rotate(140deg); }
            #r19:checked ~ .outer-circle img.outer { transform: rotate(210deg); }
            #r20:checked ~ .outer-circle img.outer { transform: rotate(280deg); }
            #r21:checked ~ .outer-circle img.outer { transform: rotate(350deg); }
            #r22:checked ~ .outer-circle img.outer { transform: rotate(420deg); }
            #r23:checked ~ .outer-circle img.outer { transform: rotate(490deg); }

            .button-group {
                margin-top: 10px;
                display: flex;
                gap: 10px;
                flex-wrap: wrap;
                justify-content: center;
                flex-direction: column;
                align-items: center;
            }

            .button-group label {
                background-color: #444;
                color: white;
                padding: 4px 8px;
                border-radius: 4px;
                cursor: pointer;
                font-size: 16px;
                width: fit-content;
            }
    
            .invisible {
    	        visibility: hidden;
            }
    
            .noDisplay {
    	        display: none;
            }
    
            .reset_visible {
    	        visibility: visible;
            }

            .button-group label:hover {
                background-color: #222;
            }

            #r0:checked ~ .button-group label[for="r1"],
            #r1:checked ~ .button-group label[for="r2"],
            #r2:checked ~ .button-group label[for="r3"],
            #r3:checked ~ .button-group label[for="r4"],
            #r4:checked ~ .button-group label[for="r5"],
            #r5:checked ~ .button-group label[for="r6"],
            #r6:checked ~ .button-group label[for="r7"] {
                display: inline-block;
            }
            #r7:checked ~ .button-group label[for="r7"] {
                display: inline-block;
                visibility: hidden;
            }
            #r0:checked ~ .button-group label[for="r0"] {
                visibility: hidden;
            }
    
            #r8:checked ~ .button-group label[for="r9"],
            #r9:checked ~ .button-group label[for="r10"],
            #r10:checked ~ .button-group label[for="r11"],
            #r11:checked ~ .button-group label[for="r12"],
            #r12:checked ~ .button-group label[for="r13"],
            #r13:checked ~ .button-group label[for="r14"],
            #r14:checked ~ .button-group label[for="r15"] {
                display: inline-block;
            }
            #r15:checked ~ .button-group label[for="r15"] {
                display: inline-block;
                visibility: hidden;
            }
            #r8:checked ~ .button-group label[for="r8"] {
                visibility: hidden;
            }
            #r8:checked ~ .button-group label[for="img2"] {
                visibility: hidden;
            }
    
            #r16:checked ~ .button-group label[for="r17"],
            #r17:checked ~ .button-group label[for="r18"],
            #r18:checked ~ .button-group label[for="r19"],
            #r19:checked ~ .button-group label[for="r20"],
            #r20:checked ~ .button-group label[for="r21"],
            #r21:checked ~ .button-group label[for="r22"],
            #r22:checked ~ .button-group label[for="r23"] {
                display: inline-block;
            }
            #r23:checked ~ .button-group label[for="r23"] {
                display: inline-block;
                visibility: hidden;
            }
            #r16:checked ~ .button-group label[for="r16"] {
                visibility: hidden;
            }

            #img0:checked ~ #container1 {
                display: flex;
            }
            #img0:checked ~ #container1 input#r1:checked ~ .button-group label[for="img1"],
            #img0:checked ~ #container1 input#r2:checked ~ .button-group label[for="img1"],
            #img0:checked ~ #container1 input#r3:checked ~ .button-group label[for="img1"],
            #img0:checked ~ #container1 input#r4:checked ~ .button-group label[for="img1"],
            #img0:checked ~ #container1 input#r5:checked ~ .button-group label[for="img1"],
            #img0:checked ~ #container1 input#r6:checked ~ .button-group label[for="img1"],
            #img0:checked ~ #container1 input#r7:checked ~ .button-group label[for="img1"] {
                visibility: visible;
            }

            #img1:checked ~ #container2 {
                display: flex;
            }
            #img1:checked ~ #container2 input#r9:checked ~ .button-group label[for="img2"],
            #img1:checked ~ #container2 input#r10:checked ~ .button-group label[for="img2"],
            #img1:checked ~ #container2 input#r11:checked ~ .button-group label[for="img2"],
            #img1:checked ~ #container2 input#r12:checked ~ .button-group label[for="img2"],
            #img1:checked ~ #container2 input#r13:checked ~ .button-group label[for="img2"],
            #img1:checked ~ #container2 input#r14:checked ~ .button-group label[for="img2"],
            #img1:checked ~ #container2 input#r15:checked ~ .button-group label[for="img2"] {
                visibility: visible;
            }

            #img2:checked ~ .captcha-frame #container3 {
                display: flex;
            }
            #img2:checked ~ .captcha-frame #container3 .button-group label[for="img2"] {
                visibility: hidden;
            }
        </style>';

        echo <<<HTML
            <div class="captcha-container" style="display: flex; align-items: center; gap: 8px;">
                <input type="radio" name="imageSet" id="img2">
                <input type="checkbox" id="captchaCheck" class="captcha-checkbox" name="captcha">
                <label for="captchaCheck" style="user-select: none;">{$this->noRobot_text}</label>

                <div class="captcha-frame">
                    <input type="radio" name="imageSet" id="img0" checked>
                    <input type="radio" name="imageSet" id="img1">


                    <div id="container1" class="container">
                        <input type="radio" name="rotation" id="r0" value="r0" checked>
                        <input type="radio" name="rotation" id="r1" value="r1">
                        <input type="radio" name="rotation" id="r2" value="r2">
                        <input type="radio" name="rotation" id="r3" value="r3">
                        <input type="radio" name="rotation" id="r4" value="r4">
                        <input type="radio" name="rotation" id="r5" value="r5">
                        <input type="radio" name="rotation" id="r6" value="r6">
                        <input type="radio" name="rotation" id="r7" value="r7">

                        <div class="outer-circle">
                            <img src="data:image/jpeg;base64,{$img1_r}" class="outer" alt="Bild aussen">
                            <img src="data:image/jpeg;base64,{$img1_o}" class="inner-image" alt="Bild innen">
                            <div class="circle"></div>
                        </div>

                        <div class="button-group">
                            <label class="invisible" for="img1">{$this->next_text}</label>
      
                            <label class="noDisplay" for="r1">{$this->rotate_text}</label>
                            <label class="noDisplay" for="r2">{$this->rotate_text}</label>
                            <label class="noDisplay" for="r3">{$this->rotate_text}</label>
                            <label class="noDisplay" for="r4">{$this->rotate_text}</label>
                            <label class="noDisplay" for="r5">{$this->rotate_text}</label>
                            <label class="noDisplay" for="r6">{$this->rotate_text}</label>
                            <label class="noDisplay" for="r7">{$this->rotate_text}</label>
                            <label class="noDisplay" for="r0">{$this->rotate_text}</label>
      
                            <label class="reset_visible" for="r0">{$this->reset_text}</label>
                        </div>
                    </div>
  
                    <div id="container2" class="container">
                        <input type="radio" name="rotation2" id="r8" value="r8" checked>
                        <input type="radio" name="rotation2" id="r9" value="r9">
                        <input type="radio" name="rotation2" id="r10" value="r10">
                        <input type="radio" name="rotation2" id="r11" value="r11">
                        <input type="radio" name="rotation2" id="r12" value="r12">
                        <input type="radio" name="rotation2" id="r13" value="r13">
                        <input type="radio" name="rotation2" id="r14" value="r14">
                        <input type="radio" name="rotation2" id="r15" value="r15">
                        <div class="outer-circle">
                            <img src="data:image/jpeg;base64,{$img2_r}" class="outer" alt="Bild außen">
                            <img src="data:image/jpeg;base64,{$img2_o}" class="inner-image" alt="Bild innen">
                            <div class="circle"></div>
                        </div>
                        <div class="button-group">
                            <label for="img2">{$this->next_text}</label>
      
                            <label class="noDisplay" for="r9">{$this->rotate_text}</label>
                            <label class="noDisplay" for="r10">{$this->rotate_text}</label>
                            <label class="noDisplay" for="r11">{$this->rotate_text}</label>
                            <label class="noDisplay" for="r12">{$this->rotate_text}</label>
                            <label class="noDisplay" for="r13">{$this->rotate_text}</label>
                            <label class="noDisplay" for="r14">{$this->rotate_text}</label>
                            <label class="noDisplay" for="r15">{$this->rotate_text}</label>
                            <label class="noDisplay" for="r8">{$this->rotate_text}</label>
      
                            <label class="reset_visible" for="r8">{$this->reset_text}</label>
                        </div>
                    </div>
  
                    <div id="container3" class="container">
                        <input type="radio" name="rotation3" id="r16" value="r16" checked>
                        <input type="radio" name="rotation3" id="r17" value="r17">
                        <input type="radio" name="rotation3" id="r18" value="r18">
                        <input type="radio" name="rotation3" id="r19" value="r19">
                        <input type="radio" name="rotation3" id="r20" value="r20">
                        <input type="radio" name="rotation3" id="r21" value="r21">
                        <input type="radio" name="rotation3" id="r22" value="r22">
                        <input type="radio" name="rotation3" id="r23" value="r23">
                        <div class="outer-circle">
                            <img src="data:image/jpeg;base64,{$img3_r}" class="outer" alt="Bild außen">
                            <img src="data:image/jpeg;base64,{$img3_o}" class="inner-image" alt="Bild innen">
                        <div class="circle"></div>
                    </div>
    
                    <div class="button-group">
                        <label for="img2">{$this->next_text}</label>
    
                        <label class="noDisplay" for="r17">{$this->rotate_text}</label>
                        <label class="noDisplay" for="r18">{$this->rotate_text}</label>
                        <label class="noDisplay" for="r19">{$this->rotate_text}</label>
                        <label class="noDisplay" for="r20">{$this->rotate_text}</label>
                        <label class="noDisplay" for="r21">{$this->rotate_text}</label>
                        <label class="noDisplay" for="r22">{$this->rotate_text}</label>
                        <label class="noDisplay" for="r23">{$this->rotate_text}</label>
                        <label class="noDisplay" for="r16">{$this->rotate_text}</label>
      
                        <label class="reset_visible" for="r16">{$this->reset_text}</label>
                    </div>
                </div>
            </div>
        HTML;
    }

    public function isVerified() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return false;
        }
        
        // Timeout prüfen
        $start = $_SESSION['captcha_start'] ?? null;
        $limit = $_SESSION['captcha_timeout'] ?? 60; // fallback 60s

         if ($start === null) {
             return false; // es gab kein Captcha vorher
         }

         if (time() - $start > $limit) {
         	unset($_SESSION['captcha_start']);
             unset($_SESSION['captcha_timeout']);
             $prefix = "captcha_rotation_";

             foreach ($_SESSION as $key => $value) {
                 if (strpos($key, $prefix) === 0) {
                     unset($_SESSION[$key]);
                 }
             }
             return "timeout";
         }

        $rotationMapping = [
            0 => [
                290 => 'r1',
                220 => 'r2',
                150 => 'r3',
                80  => 'r4',
                10  => 'r5',
            ],
            1 => [
                290 => 'r9',
                220 => 'r10',
                150 => 'r11',
                80  => 'r12',
                10  => 'r13',
            ],
            2 => [
                290 => 'r17',
                220 => 'r18',
                150 => 'r19',
                80  => 'r20',
                10  => 'r21',
            ]
        ];

        //Group names in the form
        $postKeys = ['rotation', 'rotation2', 'rotation3'];

        foreach ([0, 1, 2] as $i) {
            $rotation = $_SESSION['captcha_rotation_' . $i] ?? null;
            $expectedId = $rotationMapping[$i][$rotation] ?? null;
            $userInput = $_POST[$postKeys[$i]] ?? null;

            if ($expectedId === null || $userInput !== $expectedId) {
            	unset($_SESSION['captcha_start']);
                unset($_SESSION['captcha_timeout']);
                $prefix = "captcha_rotation_";

                foreach ($_SESSION as $key => $value) {
                    if (strpos($key, $prefix) === 0) {
                        unset($_SESSION[$key]);
                    }
                }
            	return false;
            }
        }
        unset($_SESSION['captcha_start']);
        unset($_SESSION['captcha_timeout']);
        $prefix = "captcha_rotation_";

        foreach ($_SESSION as $key => $value) {
            if (strpos($key, $prefix) === 0) {
                unset($_SESSION[$key]);
            }
        }
        return true;
    }


}
