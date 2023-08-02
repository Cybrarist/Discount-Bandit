
# Changelog

## 01/08/2023

### New Features
- Capturing ratings and stars
- add fetch  button and delete button to the view/edit pages
- Added Price History (least price recorded daily) to the product for all stores attached.
- display last updated time per service on product page.


###Improvements
- update products depending on the last updated time,instead of redoing it from the beginning.
- add extra user agents
- don't show disabled and deleted Services when attaching a product to service.
- update url to not include parameters passed to it

### Bugs Fixed

- add /gp/ url, which are products accessed from the cart / wishlist.
- fix second pricing method getting only thousands digits.
- fix currency migration will create new copy in database.
- update product price before sending notification
- problem with entry point in docker, should be solved with this update
- fixed products not allowed to add from domains.
- fix seller not being fetched properly
- update product price before sending notification
- update dockerfile to fix issue not finding Docker folder
- fixed products not allowed to add from domains.
- fix seller not being fetched properly

### Stores Added
- Amazon Poland