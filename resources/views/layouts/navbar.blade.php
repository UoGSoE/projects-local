<nav class="navbar" role="navigation" aria-label="main navigation">
  <div class="navbar-brand">
    <a href="{{route('home')}}" class="navbar-item">
      <img src="{{asset('images/logo.gif')}}" alt="UOG-Logo">
    </a>
    <p class="navbar-item">
      School of Engineering Projects
    </p>
    @auth
    <a role="button" class="navbar-burger" aria-label="menu" aria-expanded="false">
      <span aria-hidden="true"></span>
      <span aria-hidden="true"></span>
      <span aria-hidden="true"></span>
    </a>
    @endauth
  </div>

  <div class="navbar-menu" id="navMenu">
    @auth
    <div class="navbar-start">
      @if (auth()->user()->isAdmin())
      <div class="navbar-item has-dropdown is-hoverable">
        <a class="navbar-link">Admin</a>
        <div class="navbar-dropdown">

          <a class="navbar-item" href="{{ route('admin.project.index', 'undergrad') }}">
            Undergrad Projects
          </a>
          <a class="navbar-item" href="{{ route('admin.project.index', 'postgrad') }}">
            Postgrad Projects
          </a>

          <hr class="navbar-divider" />

          <a class="navbar-item" href="{{ route('admin.course.index') }}">
            Courses
          </a>
          <a class="navbar-item" href="{{ route('admin.programme.index') }}">
            Programmes
          </a>
          <a class="navbar-item" href="{{ route('researcharea.index') }}">
            Research Areas
          </a>

          <hr class="navbar-divider" />

          <a class="navbar-item" href="{{ route('admin.users', 'staff') }}">
            Staff
          </a>
          <a class="navbar-item" href="{{ route('admin.users', 'undergrad') }}">
            Undergrads
          </a>
          <a class="navbar-item" href="{{ route('admin.users', 'postgrad') }}">
            Postgrads
          </a>

          <hr class="navbar-divider" />

          <a class="navbar-item" href="{{ route('admin.student.choices', 'undergrad') }}">
            Undergrad Bulk Accept
          </a>
          <a class="navbar-item" href="{{ route('admin.student.choices', 'postgrad') }}">
            Postgrad Bulk Accept
          </a>

          <hr class="navbar-divider" />

          <a class="navbar-item" href="{{ route('project.import.allocations-page') }}">
            Import Student Allocations
          </a>

          <hr class="navbar-divider" />

          <a class="navbar-item" href="{{ route('admin.activitylog') }}">
            Activity Log
          </a>
        </div>
      </div>
      @endif
    </div>
    <div class="navbar-end">

      @if (session('original_id'))
      <form method="POST" action="{{ route('impersonate.stop') }}" class="navbar-item">
        {{ csrf_field() }}
        {{ method_field('DELETE') }}
        <button class="button">
          Stop Impersonating
        </button>
      </form>
      @endif
      <div class="navbar-item has-dropdown is-hoverable">
        <a href="" class="navbar-link">
          {{ Auth::user()->full_name }}
        </a>
        <div class="navbar-dropdown is-right">
          <form method="POST" action="{{ url('/logout') }}" class="navbar-item">
            {{ csrf_field() }}
            <a href="#" onclick="this.parentNode.submit()">Log Out</a>
          </form>
        </div>
      </div>

    </div>
    @endauth
  </div>
</nav>
<script>
  document.addEventListener('DOMContentLoaded', function() {

    // Get all "navbar-burger" elements
    var $navbarBurgers = Array.prototype.slice.call(document.querySelectorAll('.navbar-burger'), 0);

    // Check if there are any navbar burgers
    if ($navbarBurgers.length > 0) {

      // Add a click event on each of them
      $navbarBurgers.forEach(function($el) {
        $el.addEventListener('click', function() {

          // Get the target from the "data-target" attribute
          var target = $el.dataset.target;
          var $target = document.getElementById(target);

          // Toggle the class on both the "navbar-burger" and the "navbar-menu"
          $el.classList.toggle('is-active');
          $target.classList.toggle('is-active');

        });
      });
    }

  });
</script>