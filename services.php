<?php
session_start();
require_once "connect.php";
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <title>BELLEZZA</title>
    <meta charset="utf-8" />
    <meta
      name="viewport"
      content="width=device-width, initial-scale=1, shrink-to-fit=no"
    />

    <link
      href="https://fonts.googleapis.com/css?family=Montserrat:300,400,600,700"
      rel="stylesheet"
    />

    <link rel="stylesheet" href="css/open-iconic-bootstrap.min.css" />
    <link rel="stylesheet" href="css/animate.css" />

    <link rel="stylesheet" href="css/owl.carousel.min.css" />
    <link rel="stylesheet" href="css/owl.theme.default.min.css" />
    <link rel="stylesheet" href="css/magnific-popup.css" />

    <link rel="stylesheet" href="css/aos.css" />

    <link rel="stylesheet" href="css/ionicons.min.css" />

    <link rel="stylesheet" href="css/bootstrap-datepicker.css" />
    <link rel="stylesheet" href="css/jquery.timepicker.css" />

    <link rel="stylesheet" href="css/flaticon.css" />
    <link rel="stylesheet" href="css/icomoon.css" />
    <link rel="stylesheet" href="css/style.css" />
  </head>
  <body>
    <!-- Include your existing navigation -->
<?php include 'navbar.php'; ?>

    <section class="ftco-section">
      <div class="container">
        <div class="row">
          <div class="col-md-4 ftco-animate">
            <div class="media d-block text-center block-6 services">
              <div class="icon d-flex mb-3">
                <span class="flaticon-facial-treatment"></span>
              </div>
              <div class="media-body">
                <a href="skin.php">
                  <h3 class="heading">Skin &amp; Beauty Care</h3>
                </a>
                <p>
                  Even the all-powerful Pointing has no control about the blind
                  texts it is an almost unorthographic.
                </p>
                <p>
                  <a href="booking.php?service=skin" class="btn btn-primary">Book Now</a>
                </p>
              </div>
            </div>
          </div>
          <div class="col-md-4 ftco-animate">
            <div class="media d-block text-center block-6 services">
              <div class="icon d-flex mb-3">
                <span class="flaticon-cosmetics"></span>
              </div>
              <div class="media-body">
              <a href="makeup.php">
                <h3 class="heading">Makeup Pro</h3>
                </a>
                <p>
                  Even the all-powerful Pointing has no control about the blind
                  texts it is an almost unorthographic.
                </p>
                <p>
                  <a href="booking.php?service=makeup" class="btn btn-primary">Book Now</a>
                </p>
              </div>
            </div>
          </div>
          <div class="col-md-4 ftco-animate">
            <div class="media d-block text-center block-6 services">
              <div class="icon d-flex mb-3">
                <span class="flaticon-curl"></span>
              </div>
              <div class="media-body">
              <a href="hair.php">
                <h3 class="heading">Hair Style</h3>
                </a> 
                <p>
                  Even the all-powerful Pointing has no control about the blind
                  texts it is an almost unorthographic.
                </p>
                <p>
                  <a href="booking.php?service=hair" class="btn btn-primary">Book Now</a>
                </p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <section
      class="ftco-section ftco-discount img"
      style="background-image: url(images/bg_2.jpg)"
    >
      <div class="overlay"></div>
      <div class="container">
        <div class="row justify-content-end">
          <div class="col-md-5 discount ftco-animate">
            <h3>Save up to 25% Off</h3>
            <h2 class="mb-4">Student Discount</h2>
            <p class="mb-4">
              Even the all-powerful Pointing has no control about the blind
              texts it is an almost unorthographic life One day however a small
              line of blind text by the name of Lorem Ipsum decided to leave for
              the far World of Grammar.
            </p>
            <p>
              <a href="booking.php" class="btn btn-white btn-outline-white px-4 py-3">Book Now</a>
            </p>
          </div>
        </div>
      </div>
    </section>

    <section
      class="ftco-section ftco-counter img"
      id="section-counter"
      style="background-image: url(images/bg_4.jpg)"
    >
      <div class="overlay"></div>
      <div class="container">
        <div class="row justify-content-center">
          <div class="col-md-10">
            <div class="row">
              <div
                class="col-md-6 col-lg-3 d-flex justify-content-center counter-wrap ftco-animate"
              >
                <div class="block-18 text-center">
                  <div class="text">
                    <div class="icon">
                      <span class="flaticon-flower"></span>
                    </div>
                    <span>Makeup Over Done</span>
                    <strong class="number" data-number="3500">0</strong>
                  </div>
                </div>
              </div>
              <div
                class="col-md-6 col-lg-3 d-flex justify-content-center counter-wrap ftco-animate"
              >
                <div class="block-18 text-center">
                  <div class="text">
                    <div class="icon">
                      <span class="flaticon-flower"></span>
                    </div>
                    <span>Procedure</span>
                    <strong class="number" data-number="1000">0</strong>
                  </div>
                </div>
              </div>
              <div
                class="col-md-6 col-lg-3 d-flex justify-content-center counter-wrap ftco-animate"
              >
                <div class="block-18 text-center">
                  <div class="text">
                    <div class="icon">
                      <span class="flaticon-flower"></span>
                    </div>
                    <span>Happy Client</span>
                    <strong class="number" data-number="3000">0</strong>
                  </div>
                </div>
              </div>
              <div
                class="col-md-6 col-lg-3 d-flex justify-content-center counter-wrap ftco-animate"
              >
                <div class="block-18 text-center">
                  <div class="text">
                    <div class="icon">
                      <span class="flaticon-flower"></span>
                    </div>
                    <span>Skin Treatment</span>
                    <strong class="number" data-number="900">0</strong>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <footer class="ftco-footer ftco-section img">
      <div class="overlay"></div>
      <div class="container">
        <div class="row mb-5">
          <div class="col-md-3">
            <div class="ftco-footer-widget mb-4">
              <h2 class="ftco-heading-2">About Us</h2>
              <p>
                Far far away, behind the word mountains, far from the countries
                Vokalia and Consonantia, there live the blind texts.
              </p>
              <ul
                class="ftco-footer-social list-unstyled float-md-left float-lft mt-5"
              >
                <li class="ftco-animate">
                  <a href="#"><span class="icon-twitter"></span></a>
                </li>
                <li class="ftco-animate">
                  <a href="#"><span class="icon-facebook"></span></a>
                </li>
                <li class="ftco-animate">
                  <a href="#"><span class="icon-instagram"></span></a>
                </li>
              </ul>
            </div>
          </div>
          <div class="col-md-4">
            <div class="ftco-footer-widget mb-4">
              <h2 class="ftco-heading-2">Recent Blog</h2>
              <div class="block-21 mb-4 d-flex">
                <a
                  class="blog-img mr-4"
                  style="background-image: url(images/image_1.jpg)"
                ></a>
                <div class="text">
                  <h3 class="heading">
                    <a href="#"
                      >Even the all-powerful Pointing has no control about</a
                    >
                  </h3>
                  <div class="meta">
                    <div>
                      <a href="#"
                        ><span class="icon-calendar"></span> July 12, 2018</a
                      >
                    </div>
                    <div>
                      <a href="#"><span class="icon-person"></span> Admin</a>
                    </div>
                    <div>
                      <a href="#"><span class="icon-chat"></span> 19</a>
                    </div>
                  </div>
                </div>
              </div>
              <div class="block-21 mb-4 d-flex">
                <a
                  class="blog-img mr-4"
                  style="background-image: url(images/image_2.jpg)"
                ></a>
                <div class="text">
                  <h3 class="heading">
                    <a href="#"
                      >Even the all-powerful Pointing has no control about</a
                    >
                  </h3>
                  <div class="meta">
                    <div>
                      <a href="#"
                        ><span class="icon-calendar"></span> July 12, 2018</a
                      >
                    </div>
                    <div>
                      <a href="#"><span class="icon-person"></span> Admin</a>
                    </div>
                    <div>
                      <a href="#"><span class="icon-chat"></span> 19</a>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="col-md-2">
            <div class="ftco-footer-widget mb-4 ml-md-4">
              <h2 class="ftco-heading-2">Spa Center</h2>
              <ul class="list-unstyled">
                <li><a href="#" class="py-2 d-block">Body Care</a></li>
                <li><a href="#" class="py-2 d-block">Hair Care</a></li>
                <li><a href="#" class="py-2 d-block">Skin Care</a></li>
                <li><a href="#" class="py-2 d-block">Makeup</a></li>
              </ul>
            </div>
          </div>
          <div class="col-md-3">
            <div class="ftco-footer-widget mb-4">
              <h2 class="ftco-heading-2">Have a Questions?</h2>
              <div class="block-23 mb-3">
                <ul>
                  <li>
                    <span class="icon icon-map-marker"></span
                    ><span class="text"
                      >203 Fake St. Mountain View, San Francisco, California,
                      USA</span
                    >
                  </li>
                  <li>
                    <a href="#"
                      ><span class="icon icon-phone"></span
                      ><span class="text">+2 392 3929 210</span></a
                    >
                  </li>
                  <li>
                    <a href="#"
                      ><span class="icon icon-envelope"></span
                      ><span class="text">info@yourdomain.com</span></a
                    >
                  </li>
                </ul>
              </div>
            </div>
          </div>
        </div>
        <div class="row">
          <div class="col-md-12 text-center">
            <p>
              <!-- Link back to Colorlib can't be removed. Template is licensed under CC BY 3.0. -->
              
              <script>
                document.write(new Date().getFullYear());
              </script>
              <i class="icon-heart" aria-hidden="true"></i>
              <a href="https://colorlib.com" target="_blank"></a>
              <!-- Link back to Colorlib can't be removed. Template is licensed under CC BY 3.0. -->
            </p>
          </div>
        </div>
      </div>
    </footer>

    <!-- loader -->
    <div id="ftco-loader" class="show fullscreen">
      <svg class="circular" width="48px" height="48px">
        <circle
          class="path-bg"
          cx="24"
          cy="24"
          r="22"
          fill="none"
          stroke-width="4"
          stroke="#eeeeee"
        />
        <circle
          class="path"
          cx="24"
          cy="24"
          r="22"
          fill="none"
          stroke-width="4"
          stroke-miterlimit="10"
          stroke="#F96D00"
        />
      </svg>
    </div>

    <script src="js/jquery.min.js"></script>
    <script src="js/jquery-migrate-3.0.1.min.js"></script>
    <script src="js/popper.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <script src="js/jquery.easing.1.3.js"></script>
    <script src="js/jquery.waypoints.min.js"></script>
    <script src="js/jquery.stellar.min.js"></script>
    <script src="js/owl.carousel.min.js"></script>
    <script src="js/jquery.magnific-popup.min.js"></script>
    <script src="js/aos.js"></script>
    <script src="js/jquery.animateNumber.min.js"></script>
    <script src="js/bootstrap-datepicker.js"></script>
    <script src="js/jquery.timepicker.min.js"></script>
    <script src="js/scrollax.min.js"></script>
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBVWaKrjvy3MaE7SQ74_uJiULgl1JY0H2s&sensor=false"></script>
    <script src="js/google-map.js"></script>
    <script src="js/main.js"></script>
  </body>
</html>
