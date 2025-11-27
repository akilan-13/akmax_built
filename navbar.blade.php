@php
$containerNav = $containerNav ?? 'container-fluid';
$navbarDetached = ($navbarDetached ?? '');
@endphp
<style>
.navbar-search-wrapper {
  position: relative;
  width: 250px; /* adjust width as needed */
}

.navbar-search-wrapper input.form-control {
  width: 100%;
  padding-left: 40px; /* space for the icon */
  border-radius: 12px;
  background: rgba(153, 152, 152, 0.2);
  border-right: 1px solid #eee;
  /* backdrop-filter: blur(8px);
  border: 1px solid rgba(0,0,0,0.2); */
  color: #000;
}

.navbar-search-wrapper i.mdi {
  position: absolute;
  left: 12px;
  top: 50%;
  transform: translateY(-50%);
  pointer-events: none; /* so click goes to input */
  color: black;
}
</style>



<!-- Navbar -->
@if(isset($navbarDetached) && $navbarDetached == 'navbar-detached')
<nav class="layout-navbar {{$containerNav}} navbar navbar-expand-xl {{$navbarDetached}} align-items-center" style="background-color: #ffffff !important;" id="layout-navbar">
  @endif
  @if(isset($navbarDetached) && $navbarDetached == '')
<nav class="layout-navbar navbar navbar-expand-xl align-items-center" style="background-color: #ffffff !important;" id="layout-navbar">
  <div class="{{$containerNav}}">
    @endif

    <!--  Brand demo (display only for navbar-full and hide on below xl) -->
    @if(isset($navbarFull))
      <div class="navbar-brand app-brand demo d-none d-xl-flex py-0 me-4">
        <a href="{{url('/')}}" class="app-brand-link gap-2">
        <img src="{{asset('assets/common/logo_full.png')}}" class="app-brand-logo demo" alt="EGC Logo">
        <span class="app-brand-text demo menu-text fw-bold">EGC</span>
      </a>
      @if(isset($menuHorizontal))
        <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto d-block d-xl-none">
          <i class="mdi mdi-close align-middle"></i>
        </a>
      @endif
    </div>
    @endif
   
    
          <!-- ! Not required for layout-without-menu -->
          @if(!isset($navbarHideToggle))
          <div class="layout-menu-toggle navbar-nav align-items-xl-center me-3 me-xl-0{{ isset($menuHorizontal) ? ' d-xl-none ' : '' }} {{ isset($contentNavbar) ?' d-xl-none ' : '' }}">
            <a class="nav-item nav-link px-0 me-xl-4" href="javascript:void(0)">
              <i class="mdi mdi-menu mdi-24px"></i>
            </a>
          </div>
          @endif

    <div class="navbar-nav-right d-flex align-items-center" id="navbar-collapse">

      <ul class="navbar-nav flex-row align-items-center ms-auto mx-4 px-2">
          <!-- Search -->
           <li class="nav-item me-2 me-xl-1">
              <form id="switch-erp-form" action="{{ route('switch.erp.portal') }}" method="POST" style="display:none;">
                  @csrf
              </form>

              <button type="button" id="switch-erp-btn" class="btn btn-primary">
                  Switch ERP Portal
              </button>
            </li>
          <!-- /Search -->
                <!-- Notification -->
        <li class="nav-item me-2 me-xl-1">
          <a href="{{ url('/settings/general_settings') }}" aria-expanded="false" >
            <i class="mdi mdi-cog-outline fs-3 text-black"></i>
          </a>
        </li>
        <!--/ Notification -->

        <!-- Notification -->
        <li class="nav-item dropdown-notifications navbar-dropdown dropdown me-2 me-xl-1">
          <a href="javascript:void(0);" aria-expanded="false" data-bs-toggle="offcanvas" data-bs-target="#notification_tab">
            <!-- <img id="envelope" src="{{ url('assets/egc_images/notification/envelope.png') }}" alt="bell" class="bell" width="25" height="25" /> -->
            <img id="envelope" src="{{ url('assets/egc_images/notification/unread_msg_1.gif') }}" alt="bell" class="bell" width="40" height="40" />            
          </a>  
        </li>
        <!--/ Notification -->

        <!-- User -->
        <li class="nav-item navbar-dropdown dropdown-user dropdown">
          <a class="nav-link dropdown-toggle hide-arrow" href="javascript:void(0);" data-bs-toggle="dropdown">
            <div class="avatar avatar-online" style="width: 40px; height: 40px;">
               @if(Auth::user()->staff->company_type==1)
                <img  src="{{ Auth::user() && Auth::user()->staff && Auth::user()->staff->staff_image && file_exists(public_path('staff_images/Management/' . Auth::user()->staff->staff_image))
                                    ? asset('staff_images/Management/' . Auth::user()->staff->staff_image)
                                    : asset('assets/egc_images/auth/user_1.png') }}" alt="Profile" class="rounded-circle" style="border: 2px solid #ab2b22ff;" />
                @else
                <img  src="{{ Auth::user() && Auth::user()->staff && Auth::user()->staff->staff_image && file_exists(public_path('staff_images/Buisness/'.Auth::user()->staff->company_id.'/'.Auth::user()->staff->entity_id.'/' . Auth::user()->staff->staff_image))
                                    ? asset('staff_images/Buisness/'.Auth::user()->staff->company_id.'/'.Auth::user()->staff->entity_id.'/' . Auth::user()->staff->staff_image)
                                    : asset('assets/egc_images/auth/user_1.png') }}" alt="Profile" class="rounded-circle" style="border: 2px solid #ab2b22ff;" />
                @endif
            </div>
          </a>
          <ul class="dropdown-menu dropdown-menu-end">
            <li>
              <a class="dropdown-item" href="javascript:;">
                <div class="d-flex">
                  <div class="flex-shrink-0 me-3">
                    <div class="avatar avatar-online">
                        @if(Auth::user()->staff->company_type==1)
                        <img  src="{{ Auth::user() && Auth::user()->staff && Auth::user()->staff->staff_image && file_exists(public_path('staff_images/Management/' . Auth::user()->staff->staff_image))
                                            ? asset('staff_images/Management/' . Auth::user()->staff->staff_image)
                                            : asset('assets/egc_images/auth/user_1.png') }}" alt="Profile" class="w-px-40 h-auto rounded-circle" />
                        @else
                        <img  src="{{ Auth::user() && Auth::user()->staff && Auth::user()->staff->staff_image && file_exists(public_path('staff_images/Buisness/'.Auth::user()->staff->company_id.'/'.Auth::user()->staff->entity_id.'/' . Auth::user()->staff->staff_image))
                                            ? asset('staff_images/Buisness/'.Auth::user()->staff->company_id.'/'.Auth::user()->staff->entity_id.'/' . Auth::user()->staff->staff_image)
                                            : asset('assets/egc_images/auth/user_1.png') }}" alt="Profile" class="w-px-40 h-auto rounded-circle" />
                        @endif
                    </div>
                  </div>
                  <div class="flex-grow-1">
                    <span class="fw-medium d-block">
                      @if (Auth::check())
                      {{ Auth::user()->name }}
                      @else
                      Super Admin
                      @endif
                    </span>
                    <small class="text-muted">Admin</small>
                  </div>
                </div>
              </a>
            </li>
            <li>
              <div class="dropdown-divider"></div>
            </li>
            <li>
              <a class="dropdown-item" href="javascript:;" data-bs-toggle="modal" data-bs-target="#kt_modal_profile">
                <!-- <img src="{{ asset('assets/egc_images/auth/user_1.png') }}" alt="Loading" style="width: 30px; height: auto;"> -->
                 <i class="mdi mdi-account-circle-outline text-black fs-3"></i>
                <span class="align-middle">My Profile</span>
              </a>
            </li>
           
            @if (Auth::check())
                  <li>
                      <a class="dropdown-item" href="{{ route('logout') }}"
                          onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                          <i class='mdi mdi-logout fs-3'></i>
                          <span class="align-middle">Logout</span>
                      </a>
                  </li>
                  <form method="POST" id="logout-form" action="{{ route('logout') }}">
                      @csrf
                  </form>
              @else
                  <li>
                      <a class="dropdown-item" href="{{ Route::has('login') ? route('login') : url('auth/login-basic') }}">
                          <i class='mdi mdi-login me-2'></i>
                          <span class="align-middle">Login</span>
                      </a>
                  </li>
              @endif
           
            
          </ul>
        </li>
        <!--/ User -->
      </ul>
    </div>
    @if(!isset($navbarDetached))
  </div>
  @endif
</nav>
<!-- / Navbar -->

<!--begin::Modal - Profile Update -->
<div class="modal fade" id="kt_modal_profile" tabindex="-1" aria-hidden="true" data-bs-keyboard="false" data-bs-backdrop="static">
  <!--begin::Modal dialog-->
  <div class="modal-dialog">
    <!--begin::Modal content-->
    <div class="modal-content rounded-3 shadow-lg position-relative ">
    <form method="POST" action="{{ route('update_profile') }}" enctype="multipart/form-data">
              @csrf
      <div class="card__img">
        <img src="{{ url('assets/common/egc-image.jpg') }}" alt="background-image" class="bg-image" />
        <div class="profile-wrapper text-center position-absolute translate-middle" style="top: 120px; left:70px;">
          <div class="d-inline-block position-relative">
              @if(Auth::user()->staff->company_type==1)
            <img id="uploadedstaffimage" src="{{ Auth::user() && Auth::user()->staff && Auth::user()->staff->staff_image && file_exists(public_path('staff_images/Management/' . Auth::user()->staff->staff_image))
                                ? asset('staff_images/Management/' . Auth::user()->staff->staff_image)
                                : asset('assets/eapl_images/Super-Admin.png') }}" alt="Profile" class="profile-image" width="100" height="100" />
            @else
            <img id="uploadedstaffimage" src="{{ Auth::user() && Auth::user()->staff && Auth::user()->staff->staff_image && file_exists(public_path('staff_images/Buisness/'.Auth::user()->staff->company_id.'/'.Auth::user()->staff->entity_id.'/' . Auth::user()->staff->staff_image))
                                ? asset('staff_images/Buisness/'.Auth::user()->staff->company_id.'/'.Auth::user()->staff->entity_id.'/' . Auth::user()->staff->staff_image)
                                : asset('assets/eapl_images/Super-Admin.png') }}" alt="Profile" class="profile-image" width="100" height="100" />
            @endif
            <div id="imagePreview" class="preview-box">
              <img id="previewImage" src="" alt="Preview" />
            </div>
            <label for="profileImageUpload"
              class="position-absolute bottom-0 end-0 bg-white p-2 rounded-circle "
              style="cursor: pointer;">
              <i class="fas fa-camera text-primary"></i>
            </label>
          </div>
          <input type="file" name="staff_image" id="profileImageUpload" class="d-none upload_staff_image" />
        </div>
        <div class="d-flex align-items-start justify-content-center flex-column px-10 px-lg-20 mx-2">
          <label class="card__title" data-bs-toggle="tooltip" data-bs-placement="bottom" title="User Name">Super Admin</label>
          <label class="card__subtitle text-center mt-0 pt-0" data-bs-toggle="tooltip" data-bs-placement="bottom" title="User Role">Super Admin</label>
        </div>
      </div>
      <div class="modal-body pt-0 mt-5 pb-4 px-10 px-xl-20 bg-white">
        <div class="mb-4">
            <label class="fw-bold text-black">Username</label>
            <div class="form-control bg-light border-0">{{ Auth::user()->staff->user_name }}</div>
            <label class="fw-bold text-muted">Password</label>
            <div class="input-group">
                <input type="password" name="staff_password" class="form-control" id="passwordInput" placeholder="Enter New Password" value="{{ Auth::user()->staff->password }}">
                <span class="input-group-text toggle-password" style="cursor: pointer;">
                    <i class="fas fa-eye-slash"></i>
                </span>
            </div>
        </div>

        <!-- Submit Button -->
        <div class="d-flex justify-content-between align-items-center mt-4">
          <button type="reset" class="btn btn-secondary me-3" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary px-4" data-bs-dismiss="modal">Update Profile</button>
        </div>
          <!--end::Profile Form-->
      </div>
     </form>
      <!--end::Modal body-->
    </div>
    <!--end::Modal content-->
  </div>
  <!--end::Modal dialog-->
</div>
<!--end::Modal - Profile Update -->


<!--begin:: Notification Offcanvan-->
<div class="offcanvas offcanvas-end" id="notification_tab" tabindex="-1" aria-hidden="true" data-bs-keyboard="false" data-bs-backdrop="static">
  <div class="offcanvas-header border-bottom d-block bg-label-white">
    <div class="d-flex align-items-center justify-content-between mb-3">
      <div class="d-flex align-items-center gap-1">
        <h5 class="offcanvas-title text-dark fw-bold">Notifications</h5>
        <span id="notificationCount" class="badge rounded-pill bg-danger text-white">0 New</span>
      </div>
      <button type="button" class="btn-close text-reset rounded-pill custom-bg-orange modal_close" data-bs-dismiss="offcanvas" aria-label="Close"></button>

    </div>
      <div class="d-flex align-items-center justify-content-between">
          <label class="clear-all hover-badge">
              <span class="fs-6 fw-semibold ">Clear All</span>
          </label>
          <label class="mark-all-read hover-badge">
              <span class="fs-6 fw-semibold ">Mark all as read</span>
          </label>
      </div>

  </div>
  <div class="offcanvas-body flex-grow-1">
    <ul class="list-group list-group-flush list-unstyled scrollable-container notification-list" >

    </ul>
  </div>
</div>
<!--End:: Notification Offcanvan-->

<style>


  .preview-box {
      position: absolute;
      top: 50px; /* Moved higher */
      left: 100%;
      transform: translateX(-50%) scale(0.5);
      opacity: 0;
      transition: transform 0.3s ease-in-out, opacity 0.3s ease-in-out;
      background: #fed240;
      border-radius: 12px;
      padding: 8px;
      box-shadow: 0px 4px 12px rgba(0, 0, 0, 0.25);
      z-index: 10;
  }

    .preview-box img {
        width: 100px;  /* Increased size */
        height: 100px; /* Increased size */
        border-radius: 10px;
    }
    .profile-image {
        border: 8px solid white;      /* sets border thickness and color */
        border-radius: 100% !important; /* keeps it circular */
        background-color: white;
        /* box-shadow: 0 4px 12px rgba(0, 0, 0, 0.25);  */
      }
    .profile-image:hover + .preview-box,
    .profile-image:focus + .preview-box {
        transform: translateX(-50%) scale(1);
        opacity: 1;
    }
    /*.card__img {*/
    /*  height: 192px;*/
    /*  width: 100%;*/
    /*  position: relative;*/
    /*  overflow: visible;*/
    /*}*/
   /* Remove border-radius from .card__img */
    .card__img {
      height: 192px;
      width: 100%;
      position: relative;
      overflow: visible; /* allow the image to extend beyond */
      /* border-radius removed here */
    }

    /* Glassmorphic background image for modal */
    .bg-image {
      width: 100%;
      height: 100px;             /* Fixed height */
      object-fit: cover;         /* Cover the container without stretching */
      display: block;
      border-top-left-radius: 12px;
      border-top-right-radius: 12px;
      
      /* Glassmorphism effect */
      backdrop-filter: blur(8px);       /* Blur the background behind */
      background-color: rgba(255, 255, 255, 0.25); /* Semi-transparent overlay */
      border: 1px solid rgba(255, 255, 255, 0.18); /* Optional subtle border */
    }

    /* Apply full radius to modal content */
    .modal-content {
      border-radius: 12px !important;
      overflow: hidden; /* Ensures all modal contents respect the border radius */
    }


    .card__title {
      margin-top: 50px;
      margin-bottom: 0px;
      font-weight: 600;
      font-size: 25px;
      color: black;
      padding: 0px 10px;
    }

    .card__subtitle {
      font-weight: 400;
      font-size: 18px;
      color: black;
      padding: 10px;
    }

    .profile-wrapper {
      z-index: 5;
      margin-top: -20px; /* pushes it up over SVG */

    }

    .card__icons {
    display: flex;
    justify-content: center;
    gap: 10px; /* Adds space between icons */
    margin-top: 10px;
  }

 .card__icons a {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 40px; /* Fixed width for consistent sizing */
    height: 40px; /* Fixed height */
    border-radius: 50%; /* Circular icons */
    background-color: #1877F2; /* Facebook blue, you can customize */
    transition: background-color 0.3s ease, transform 0.3s ease; /* Smooth transitions for color and scale */
  }

  .card__icons a:hover {
    background-color: #0e5bcf; /* Slightly darker shade on hover */
    transform: scale(1.1); /* Slightly larger on hover */
  }

  .card__icons i {
    font-size: 24px; /* Icon size */
  }

  .card__icons a[data-bs-toggle="tooltip"]:hover {
    text-decoration: none; /* Ensures no underlining on hover */
  }
</style>
<!-- Script -->
<script>
  document.addEventListener("DOMContentLoaded", function () {
      let profileImage = document.getElementById('uploadedstaffimage');
      let previewBox = document.getElementById('imagePreview');
      let previewImage = document.getElementById('previewImage');

      profileImage.addEventListener("mouseenter", function () {
          previewImage.src = profileImage.src;
          previewBox.style.opacity = "1";
          previewBox.style.transform = "translateX(-50%) scale(1)";
      });

      profileImage.addEventListener("mouseleave", function () {
          previewBox.style.opacity = "0";
          previewBox.style.transform = "translateX(-50%) scale(0.5)";
      });
  });
    document.addEventListener('DOMContentLoaded', () => {
      let staff_add = document.getElementById('uploadedstaffimage');
      const staff_add_fileInput = document.querySelector('.upload_staff_image');
      if (staff_add) {
          const staff_add_resetImage = staff_add.src;
          staff_add_fileInput.onchange = () => {
              if (staff_add_fileInput.files[0]) {
                  staff_add.src = window.URL.createObjectURL(staff_add_fileInput.files[0]);
              }
          };
      }
  });
</script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<script>
  document.addEventListener("DOMContentLoaded", function () {
      const passwordInput = document.getElementById("passwordInput");
      const togglePassword = document.querySelector(".toggle-password i");

      document.querySelector(".toggle-password").addEventListener("click", function () {
          if (passwordInput.type === "password") {
              passwordInput.type = "text";
              togglePassword.classList.remove("mdi-eye-off");
              togglePassword.classList.add("mdi-eye");
          } else {
              passwordInput.type = "password";
              togglePassword.classList.remove("mdi-eye");
              togglePassword.classList.add("mdi-eye-off");
          }
      });
  });
  </script>

  <!-- <script>
document.getElementById('switch-erp-btn').addEventListener('click', function () {
    document.getElementById('switch-erp-form').submit(); // ← POST
});
</script> -->

<style>

body {
 position: relative;
}
.gooey-effect {
    position: fixed; /* fixed ensures it covers full viewport */
    top: 0;
    left: 0;
    width: 100vw;
    height: 100vh;
    border-radius: 0;
    overflow: visible; /* allow blur to extend outside */
    z-index: 0;
    opacity: 0.9;
    pointer-events: none;
}

.gooey-blob {
    position: absolute;
    border-radius: 50%;
    filter: blur(12px);
    animation: float-blob 15s infinite ease-in-out;
    opacity: 0.9;
}

/* individual blob sizes and positions */
.gooey-blob:nth-child(1) {
    width: 250px; height: 250px;
    left: -50px; top: 100px;
    background: radial-gradient(circle, rgba(0,255,170,0.7) 0%, rgba(0,255,170,0) 70%);
    animation-duration: 8s;
}

.gooey-blob:nth-child(2) {
    width: 200px; height: 200px;
    right: -30px; top: 50px;
    background: radial-gradient(circle, rgba(0,179,255,0.7) 0%, rgba(0,179,255,0) 70%);
    animation-duration: 8s; animation-delay: -3s;
}

.gooey-blob:nth-child(3) {
    width: 180px; height: 180px;
    right: 50px; bottom: 100px;
    background: radial-gradient(circle, rgba(0,255,170,0.7) 0%, rgba(0,255,170,0) 70%);
    animation-duration: 10s; animation-delay: -4s;
}

.gooey-blob:nth-child(4) {
    width: 220px; height: 220px;
    left: 30px; bottom: 30px;
    background: radial-gradient(circle, rgba(0,179,255,0.7) 0%, rgba(0,179,255,0) 70%);
    animation-duration: 10s; animation-delay: -4s;
}

@keyframes float-blob {
    0%, 100% { transform: translate(0,0) scale(1); }
    20% { transform: translate(30px,20px) scale(1.05); }
    40% { transform: translate(20px,40px) scale(0.95); }
    60% { transform: translate(-20px,30px) scale(1.1); }
    80% { transform: translate(-30px,-20px) scale(0.9); }
}

/* Tunnel canvas */
#tunnelCanvas {
    position: fixed;
    top: 0; left: 0;
    width: 100vw;
    height: 100vh;
    z-index: 1; /* above gooey blobs */
    clip-path: circle(10% at 50% 50%);
    transition: clip-path 1.8s ease-out;
}

#tunnelCanvas.active {
    clip-path: circle(150% at 50% 50%);
}

/* Tunnel container */
#tunnelContainer {
    position: fixed;
    top: 0; left: 0;
    width: 100vw; height: 100vh;
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 2;
    pointer-events: none;
    transform-style: preserve-3d;
}

#tunnelContainer.active {
    pointer-events: all;
    z-index: 15;
}

/* switch overlay */
.switch-animation {
    position: fixed;
    top: 0; left: 0;
    width: 100vw; height: 100vh;
    background-color: rgba(0, 0, 0, 1);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 9999;
    overflow: hidden;
}
</style>

<!-- Gooey Background -->
<div class="gooey-effect">
  <div class="gooey-blob"></div>
  <div class="gooey-blob"></div>
  <div class="gooey-blob"></div>
  <div class="gooey-blob"></div>
</div>

<!-- Tunnel Overlay -->
<div id="switch-animation" class="switch-animation">
  <div id="tunnelContainer">
      <canvas id="tunnelCanvas"></canvas>
  </div>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>

<script>
const canvasTunnel = document.getElementById("tunnelCanvas");
const tunnelContainer = document.getElementById("tunnelContainer");

let animationQueue = [];
let isAnimating = true,
    tunnelEndPoint,
    renderFrameId,
    hoverTime = 0;

let w = window.innerWidth,
    h = window.innerHeight;
const cameraSpeed = 0.00015,
      lightSpeed = 0.001,
      tubularSegments = 1200,
      radialSegments = 12,
      tubeRadius = 3;

let renderer, scene, camera, tube;
let lights = [], path, material, pct = 0, pct2 = 0;

// ---------- Start Portal ----------
function startPortal() {
    document.body.style.backgroundImage = "none";
    document.body.style.backgroundColor = "#ffffffff";
    canvasTunnel.style.display = "block";
    tunnelContainer.style.display = "flex";
    initTunnel();
    render();
    setTimeout(() => {
        canvasTunnel.classList.add("active");
    }, 100);
}

// ---------- Circular Tunnel Path ----------
function createCircularPath() {
    const points = [];
    const totalPoints = 200;
    const controlPoints = [
        new THREE.Vector3(0, 0, 0),
        new THREE.Vector3(20, 10, -50),
        new THREE.Vector3(40, -10, -100),
        new THREE.Vector3(60, 15, -150),
        new THREE.Vector3(50, -5, -200),
        new THREE.Vector3(0, 0, -250),
        new THREE.Vector3(-100, 0, -200),
        new THREE.Vector3(-150, 0, -100),
        new THREE.Vector3(-100, 0, 0),
        new THREE.Vector3(-50, 10, 100),
        new THREE.Vector3(-20, -10, 150),
        new THREE.Vector3(0, 0, 200)
    ];
    const curve = new THREE.CatmullRomCurve3(controlPoints);
    curve.tension = 0.1;
    for (let i = 0; i < totalPoints; i++) {
        const t = i / (totalPoints - 1);
        points.push(curve.getPoint(t));
    }
    return points;
}

// ---------- Return to Home Animation ----------
function returnToHome() {
    const approachAnimation = {
        progress: 0,
        duration: 1200,
        startTime: Date.now(),
        startPosition: camera.position.clone(),
        targetPosition: new THREE.Vector3(tunnelEndPoint.x - 5, tunnelEndPoint.y, tunnelEndPoint.z - 5),
        update: function () {
            const elapsed = Date.now() - this.startTime;
            this.progress = Math.min(elapsed / this.duration, 1);
            const t = this.progress < 0.5 
                ? 4 * this.progress ** 3 
                : 1 - Math.pow(-2 * this.progress + 2, 3) / 2;
            camera.position.lerpVectors(this.startPosition, this.targetPosition, t);
            if (this.progress >= 1) startPortalTransition();
        }
    };

    function startPortalTransition() {
        const zoomAnimation = {
            progress: 0,
            duration: 800,
            startTime: Date.now(),
            startPosition: camera.position.clone(),
            targetPosition: new THREE.Vector3(tunnelEndPoint.x + 2, tunnelEndPoint.y, tunnelEndPoint.z + 2),
            update: function () {
                const elapsed = Date.now() - this.startTime;
                this.progress = Math.min(elapsed / this.duration, 1);
                const t = this.progress ** 2;
                camera.position.lerpVectors(this.startPosition, this.targetPosition, t);
                if (this.progress > 0.5 && this.progress < 0.6) {
                    scene.background = new THREE.Color(0xffffff);
                    scene.fog = null;
                } else if (this.progress >= 0.6) {
                    scene.background = new THREE.Color(0x000000);
                    scene.fog = new THREE.FogExp2(0x000000, 0.005);
                    if (this.progress >= 1) completePortalLoop();
                }
            }
        };
        animationQueue.push(zoomAnimation);
    }

    function completePortalLoop() {
        canvasTunnel.style.transition = "opacity 0.7s ease-out";
        canvasTunnel.style.opacity = "0";
        setTimeout(() => {
            canvasTunnel.style.display = "none";
        }, 700);
        cancelAnimationFrame(renderFrameId);
        isAnimating = false;
    }

    animationQueue.push(approachAnimation);
}

// ---------- Initialize Tunnel ----------
function initTunnel() {
    renderer = new THREE.WebGLRenderer({
        canvas: canvasTunnel,
        antialias: true,
        alpha: true,
        powerPreference: "high-performance"
    });
    renderer.setSize(w, h);

    scene = new THREE.Scene();
    scene.fog = new THREE.FogExp2(0x000000, 0.005);

    camera = new THREE.PerspectiveCamera(60, w / h, 0.1, 1000);

    // ---------- Stars ----------
    const starsCount = 2000;
    const starsPositions = new Float32Array(starsCount * 3);
    for (let i = 0; i < starsCount; i++) {
        starsPositions[i*3] = THREE.MathUtils.randFloatSpread(1500);
        starsPositions[i*3+1] = THREE.MathUtils.randFloatSpread(1500);
        starsPositions[i*3+2] = THREE.MathUtils.randFloatSpread(1500);
    }
    const starsGeometry = new THREE.BufferGeometry();
    starsGeometry.setAttribute("position", new THREE.BufferAttribute(starsPositions, 3));
    const starsTexture = new THREE.CanvasTexture(createCircleTexture());
    const starsMaterial = new THREE.PointsMaterial({ color: 0xffffff, size: 1, map: starsTexture, transparent: true });
    scene.add(new THREE.Points(starsGeometry, starsMaterial));

    // ---------- Tunnel ----------
    const organicPoints = createCircularPath();
    path = new THREE.CatmullRomCurve3(organicPoints);
    const tubeGeometry = new THREE.TubeBufferGeometry(path, tubularSegments, tubeRadius, radialSegments, false);
    const colors = [];
    for (let i = 0; i < tubeGeometry.attributes.position.count; i++) {
        const color = new THREE.Color(i % 2 === 0 ? "#00a3ff" : "#00ffaa");
        colors.push(color.r, color.g, color.b);
    }
    tubeGeometry.setAttribute("color", new THREE.Float32BufferAttribute(colors, 3));
    material = new THREE.MeshLambertMaterial({ side: THREE.BackSide, vertexColors: true, wireframe: true, emissive: 0x333333, emissiveIntensity: 0.4 });
    tube = new THREE.Mesh(tubeGeometry, material);
    scene.add(tube);

    // ---------- Back of Portal Card ----------
    const backOfCard = createBackOfPortalCard();
    const position = organicPoints[organicPoints.length-1];
    backOfCard.position.copy(position);
    tunnelEndPoint = position;
    backOfCard.lookAt(organicPoints[organicPoints.length-6]);
    backOfCard.userData = { isBackCard: true };
    scene.add(backOfCard);

    // ---------- Lights ----------
    scene.add(new THREE.PointLight(0xffffff, 1, 50));
    scene.add(new THREE.AmbientLight(0x555555));
    const lightColors = [0x00a3ff, 0x00ffaa, 0x00a3ff, 0x00ffaa, 0xffffff];
    for (let i=0;i<5;i++){
        const l = new THREE.PointLight(lightColors[i], 1.2, 20);
        lights.push(l);
        scene.add(l);
    }

    // ---------- Code Sprites ----------
    const thoughts = [
        "The best way to predict the future is to create it.",
        "Code is like humor. When you have to explain it, it’s bad.",
        "First, solve the problem. Then, write the code.",
        "Simplicity is the soul of efficiency.",
        "Experience is the name everyone gives to their mistakes.",
        "Make it work, make it right, make it fast.",
        "Innovation distinguishes between a leader and a follower."
    ];
    for (let i = 0; i < 100; i++) {
        const quote = thoughts[Math.floor(Math.random()*thoughts.length)];
        const sprite = createCodeSnippetSprite(quote);
        sprite.position.set((Math.random()-0.5)*400, (Math.random()-0.5)*400, (Math.random()-0.5)*400);
        scene.add(sprite);
    }

    // ---------- Resize ----------
    window.onresize = function() {
        w = window.innerWidth;
        h = window.innerHeight;
        camera.aspect = w / h;
        camera.updateProjectionMatrix();
        renderer.setSize(w,h);
    };

    // ---------- Click on Back Card ----------
    const raycaster = new THREE.Raycaster();
    const mouse = new THREE.Vector2();
    canvasTunnel.addEventListener("click", (event) => {
        mouse.x = (event.clientX / window.innerWidth) * 2 - 1;
        mouse.y = -(event.clientY / window.innerHeight) * 2 + 1;
        raycaster.setFromCamera(mouse, camera);
        const intersects = raycaster.intersectObjects(scene.children);
        intersects.forEach(obj => {
            if(obj.object.userData && obj.object.userData.isBackCard) returnToHome();
        });
    });
}

// ---------- Sprite Creation ----------
// function createCodeSnippetSprite(text) {
//     const canvas = document.createElement("canvas");
//     canvas.width = 400;
//     canvas.height = 250;
//     const ctx = canvas.getContext("2d");
//     ctx.clearRect(0,0,canvas.width,canvas.height);
//     ctx.font = "20px 'Consolas', monospace";
//     ctx.textAlign = "left"; ctx.textBaseline="top";
//     const lines = text.split("\n");
//     ctx.fillStyle = "#f8f8f2";
//     for(let i=0;i<lines.length;i++){
//         ctx.fillText(lines[i], 15, 15+i*24);
//     }
//     const texture = new THREE.CanvasTexture(canvas);
//     texture.minFilter = THREE.LinearFilter;
//     const sprite = new THREE.Sprite(new THREE.SpriteMaterial({ map: texture, transparent:true, opacity:0.8, blending:THREE.AdditiveBlending }));
//     const scaleFactor = 8 + Math.random()*12;
//     sprite.scale.set(scaleFactor, scaleFactor*(canvas.height/canvas.width),1);
//     return sprite;
// }

function createCodeSnippetSprite(text) {
    const canvas = document.createElement("canvas");
    canvas.width = 400;
    canvas.height = 250;
    const ctx = canvas.getContext("2d");
    ctx.clearRect(0, 0, canvas.width, canvas.height);
    ctx.font = "20px 'Consolas', monospace";
    ctx.textAlign = "left";
    ctx.textBaseline = "top";
    ctx.fillStyle = "#f8f8f2";

    const padding = 15;
    const maxWidth = canvas.width - padding * 2;
    const lineHeight = 24;

    const words = text.split(" ");
    let line = "";
    let y = padding;

    for (let n = 0; n < words.length; n++) {
        const testLine = line + words[n] + " ";
        const metrics = ctx.measureText(testLine);
        if (metrics.width > maxWidth && line !== "") {
            ctx.fillText(line, padding, y);
            line = words[n] + " ";
            y += lineHeight;
        } else {
            line = testLine;
        }
    }
    ctx.fillText(line, padding, y);

    const texture = new THREE.CanvasTexture(canvas);
    texture.minFilter = THREE.LinearFilter;
    const sprite = new THREE.Sprite(new THREE.SpriteMaterial({
        map: texture,
        transparent: true,
        opacity: 0.8,
        blending: THREE.AdditiveBlending
    }));

    const scaleFactor = 8 + Math.random() * 12;
    sprite.scale.set(scaleFactor, scaleFactor * (canvas.height / canvas.width), 1);

    return sprite;


}



// ---------- Circle Texture for Stars ----------
function createCircleTexture(){
    const canvas=document.createElement("canvas");
    canvas.width=32; canvas.height=32;
    const ctx=canvas.getContext("2d");
    const gradient=ctx.createRadialGradient(16,16,0,16,16,16);
    gradient.addColorStop(0,"rgba(255,255,255,1)");
    gradient.addColorStop(0.5,"rgba(255,255,255,0.5)");
    gradient.addColorStop(1,"rgba(255,255,255,0)");
    ctx.fillStyle=gradient;
    ctx.beginPath(); ctx.arc(16,16,16,0,2*Math.PI); ctx.fill();
    return canvas;
}

// ---------- Back of Portal Card ----------
function createBackOfPortalCard(){
    const geometry = new THREE.PlaneGeometry(20,28);
    const canvas = document.createElement("canvas");
    canvas.width=1280; canvas.height=1820;
    const ctx=canvas.getContext("2d");
    ctx.fillStyle="rgba(10,12,18,0.6)"; ctx.fillRect(0,0,canvas.width,canvas.height);
    ctx.font="300 40px Unica One"; ctx.fillStyle="white"; ctx.textAlign="center"; ctx.textBaseline="middle";
    ctx.shadowColor="rgba(0,255,170,0.7)"; ctx.shadowBlur=15;
    ctx.fillText("YOU'VE REACHED THE", canvas.width/2, canvas.height/2-30);
    ctx.fillText("END OF THE INTERNET", canvas.width/2, canvas.height/2+30);
    ctx.shadowBlur=0;
    const texture = new THREE.CanvasTexture(canvas);
    const material = new THREE.MeshBasicMaterial({ map: texture, transparent:true, opacity:0.9, side:THREE.DoubleSide });
    return new THREE.Mesh(geometry, material);
}

// ---------- Render Loop ----------
function render(){
    // ---------- Process Animation Queue ----------
    animationQueue.forEach(anim=>anim.update());
    animationQueue = animationQueue.filter(anim=>anim.progress<1);

    // ---------- Tunnel Camera & Lights ----------
    pct += cameraSpeed; if(pct>=0.995) pct=0;
    pct2 += lightSpeed; if(pct2>=0.995) pct2=0;

    if(pct < 0.985){
        const pt1 = path.getPointAt(pct);
        const pt2 = path.getPointAt(Math.min(pct+0.01,1));
        camera.position.set(pt1.x, pt1.y, pt1.z);
        camera.lookAt(pt2);
        lights[0].position.set(pt2.x, pt2.y, pt2.z);
        for(let i=1;i<lights.length;i++){
            const offset=((i*13)%17)/20;
            const lightPct=(pct2+offset)%0.995;
            const pos=path.getPointAt(lightPct);
            lights[i].position.set(pos.x,pos.y,pos.z);
        }
    } else {
        hoverTime+=0.02;
        const hoverOffset = Math.sin(hoverTime)*0.5;
        const base=path.getPointAt(0.985);
        const target=path.getPointAt(0.99);
        camera.position.set(base.x, base.y+hoverOffset, base.z);
        camera.lookAt(target);
    }

    renderer.render(scene,camera);
    renderFrameId=requestAnimationFrame(render);
}

// ---------- Button Trigger ----------
document.getElementById("switch-erp-btn").addEventListener("click",function(e){
    document.getElementById("switch-animation").style.display='block';
    e.preventDefault();
    
    setTimeout(()=>{ document.getElementById("switch-erp-form").submit(); },4000);
});

startPortal();
</script>
