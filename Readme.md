# Project System

Basic student project system for UoG/SoE.  This allows staff to add/edit/remove projects which students
can then choose from.  Staff/admins can then approve those choices so students are allocated
fairly.

# Staff POV

Staff can log in, add new undergrad & postgrad projects, edit them, remove them.  If students
have applied for one of their postgrad projects they can accept them _if_ it was the students
first choice.

Projects are associated with one or more courses and degree programmes (this lets the system show
appropriate projects to students once they log in).

# Student POV

Students can log in and see projects which are applicable to the course they are enrolled on.  They
can further filter that list by degree programme.  They have to pick five projects - preference
one to five.  Once they have picked five projects and they get a confirmation email to remind them of their choices.  Students can change their mind and re-choose projects *up until* they have been
accepted by an admin or member of staff - then their choice is frozen.

# Admin POV

Admins can create/edit/remove projects.  They can create degree programmes and courses.  They can
also add & remove students from courses.  They can see various reports/lists of all projects/students too.  They can also 'bulk allocate' students to projects in a clearing process if there
are students who have not been accepted by a member of staff.  They can also manually accept
students onto a specific project for both undergrad and postgrad projects.

They can also remove all students from the system in one go, or by course, or by undergrad/postgrad groupings.

Admins can also grant or remove admin rights to other users in the system.

# Notificiations

A reminder email is sent to a student when they make their project choices.  Another is sent to them
once they are accepted onto a project.

# Installation

This is a PHP Laravel app.  Please see the [laravel docs](https://laravel.com/docs) for system requirements.

## Basic test install from scratch

Clone this repository onto your server then do :

```
composer install
cp .env.example .env
# then edit the .env file to suit your setup - email server, database etc)
php artisan key:generate
php artisan migrate
php artisan db:seed --class=TestDataSeeder
php artisan serve
```

You should now be able to point your browser to the url the last command showed in it's output.  The test data creates an admin account of 'admin' / 'secret'.

## Production install

Much the same steps as above but skip the last two steps. You will want to point a real webserver at a document root of `/path/to/the/code/public/` and make sure `/storage` and `/bootstrap/cache` directories are write-able by the webserver.


