1. Run `composer install` to install project dependencies.
2. Execute `docker-compose up -d` to start MySQL and mail server.
3. Use `php bin/console doctrine:migrations:migrate` to run migrations.
4. Run `php bin/console doctrine:fixtures:load` to create an admin user.
5. Upload `minifactory.postman_collection.json` to Postman.
6. Set up an Environment with a variable `apiUrl` = `http://localhost:8000`.
7. To view emails after a purchase, access `http://localhost:8025`
8. Admin user credentials, email: `admin@admin.com` pass: `admin` 

# Notes
- Given the short amount of time to complete the bonus points, I mainly focused on making the project functional and left some best practices and didn't treat all of the corner cases.
- The total amount of time was about 6 or so hours. As I mentioned, I haven't programmed in Symfony in a while and I started a little slow, needing to consult the documentation quite often.
- Many improvements could have been made like: Treat all corner cases, Abstract the logic in the controllers to Services, enforce DRY principle, move frequently used dependencies to the constructor instead of the function injection, break bigger functions into smaller ones (Keep it simple), and others.

