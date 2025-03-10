# VTT-Shopping-site
PHP/MYSQL site for out of RGP/VTT game shopping, set characters location, shop availability and items available, leave them to pick items, update your game before next session.

Use the loginsystem.sql to setup the database
Update the config.php to your prefered settings.

DB has two layers:
admin - (username admin, password admin)
player character - (username pc, password pc)

Use these to initally test then remove them via an admin login when ready.
Create new users via register.php (this really isn't secure, it didn't have to be, it was just for a home game, feel free to adjust as you see fit).

Admin can set player location, currency and edit their inventory.
Admin can create locations, shops, items and set which shops have what items.

PC will only see the shops in the location they are set to.
So when traveling, I would set them to a location with no shops "On your travels" is what its currently called.
I'd end a session in a town/village or city and set there locations.
They can then login, make purchases prior to the next session and I could add them in the VTT near the next session. Saving some time and making the sessions less "I'd like some ball barings, is there a shop that..." kind of start to a session.

Built this for me and my group, putting it here incase its of use to someone else.
Enjoy.
