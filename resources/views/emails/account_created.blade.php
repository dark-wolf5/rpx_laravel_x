<html>

        <!-- Fonts -->
        <link href="https://fonts.googleapis.com/css?family=Nunito:200,600" rel="stylesheet">

        <!-- Styles -->
        <style>

            html,
            body {
                min-width: 100%;
                width: 100%;
                color: white;
                font-family: 'Nunito', sans-serif;
                font-weight: 200;
                height: 100vh;
                margin: 0;
                padding: 40px;
            }
            .sbLogo{
                display: block;
                position: relative;
                margin: 0 auto;
                margin-top: 20px;
                margin-bottom: 40px;
                max-width: 350px;
                width: 350px;
                border-radius: 10px;
            }
            .full-height {
                height: 100vh;
            }

            .flex-center {
                position: relative;
                margin: 0 auto;
                align-items: center;
                display: flex;
                padding: 40px;
                border-radius: 10px;
                justify-content: center;
                max-width: 650px;
                width: 96%;
                color: white;
                background-color: #32303d;
            }

            .position-ref {
                position: relative;
            }

            a {
                color: #64e56f;
                font-size: 13px;
                font-weight: 600;
                letter-spacing: .1rem;
                text-decoration: none;
                text-transform: uppercase;
            }

            .m-b-md {
                margin-bottom: 30px;
            }

            .bottomLinks  a {
                padding-left: 10px;
                padding-right: 10px;
                color: #2fed85;
            }

            .bottomLinks{
                max-width: 350px;
                width: 96%;
                padding-top: 20px;
                padding-bottom: 20px;
            }

            .bottomLinks  p {
                position: relative;
                display: block;
                margin: 0 auto;
            }

        </style>

    <body>

        <img class='sbLogo' src='https://rpx.com/assets/images/rpx_logo_header.png' />

        <div class='flex-center'>

            <p>

                <?php

                    if ($rpxUser->first_name !== '') {
                        $userWelcome = 'there';
                    } else {
                        $userWelcome = $user->username;
                    }

                    echo "Hey ". $userWelcome . "!";

                ?>

                <br/><br/>

                <?php
                    if ($rpxUser->first_name !== '') {
                        echo $rpxUser->first_name;
                    } else {
                        echo $user->username;
                    }
                ?>, you have signed up to Rpx.

                <br/><br/>

                <?php
                    //Let's use this page to redirect the user to log in from their sign up welcome e-mail.
                    $userPage = 'home';

                    //The user is not of type personal.
                    if($rpxUser->user_type != 4)
                        $userPage = 'business';
                    else
                        $userPage = 'home';
                ?>

                <?php
                    if ($withLink === true) {
                        echo "Welcome to the Rpx app, where you will be able to acquire points for your community purchases.<br/><br/>";
                        echo "We are very interested in your opinion, please don't hesitate to text +1 (786) 600-5946 for any feedback or concerns.<br/><br/>";
                        echo "Every business in the Rpx platform is different. Each of them has their unique aspects, and we pride ourselves in bringing you, the user, the best experience from them.<br/><br>";
                        echo "In order to finish creating your account, you have two options:<br/>";
                        echo "1. Reset your password at any time from the link below.<br/>";
                        echo "2. Along with this e-mail, we also sent you an e-mail that is valid for 1-hour to reset your password.<br/><br/>";
                    }
                ?>

                Just <a href='https://rpx.com/<?php echo $userPage; ?>'>click on this link</a> to log-in.

            </p>

        </div>

        <div class="flex-center bottomLinks" style="margin-top: 20px; text-align: center;">
            <p>
                <a href="https://rpx.com/">HOME</a>

                <a href="https://rpx.com/business">BUSINESS</a>
            </p>
        </div>

    </body>

</html>
