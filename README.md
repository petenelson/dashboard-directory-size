# Dashboard Directory Size
[WordPress dashboard widget](https://wordpress.org/plugins/dashboard-directory-size/) that displays a list of common WordPress directories and their sizes.  Handy if you need to keep an eye on the size of your WordPress install.  Custom directories can also be configured.

[![Code Climate](https://codeclimate.com/github/petenelson/dashboard-directory-size/badges/gpa.svg)](https://codeclimate.com/github/petenelson/dashboard-directory-size)

## Revision History

### v1.0.0 April 28, 2015
- Initial release

## Roadmap
- Add option for WordPress database size (SELECT table_schema, SUM(data_length + index_length) FROM information_schema.TABLES where table_schema = 'tablename' GROUP BY table_schema)
