=== Cart Validation for WooCommerce ===
Contributors: sahilmultani
Tags: cart validation, cart restrictions, checkout restrictions, product restrictions, order restrictions
Requires at least: 5.0
Tested up to: 6.9
Stable tag: 1.0.0
Requires PHP: 7.4
License: GPLv3 or later
License URI: https://www.gnu.org/licenses/gpl-3.0.html
WC requires at least: 5.0.0
WC tested up to: 10.5.2

Restrict WooCommerce checkout by products, categories, user roles, and more. Create powerful cart validation rules.

== Description ==

<strong>Cart Validation for WooCommerce</strong> allows store owners to create advanced conditional cart rules and restrict checkout based on products, categories, user roles, and more.

Easily prevent incompatible purchases, block restricted countries, restrict product combinations, and control checkout behavior using flexible AND/OR rule logic.

Whether you sell regulated products, wholesale items, same-day delivery products, or member-only items — this plugin gives you full control over WooCommerce cart validation.

No coding required.

== 📒 Why You Need This Plugin ==

Many WooCommerce stores face issues like:

<ul>
<li>Customers mixing incompatible products</li>
<li>Country-based product restrictions</li>
<li>Role-based purchasing limitations</li>
<li>Delivery conflicts (same-day vs standard items)</li>
<li>Regulatory compliance (alcohol, digital goods, region-specific products)</li>
</ul>

By default, WooCommerce does not allow advanced conditional checkout restrictions.

<strong>Cart Validation for WooCommerce</strong> solves this.

== 📒 Key Features ==

= 1. Enable / Disable Cart Validation =

Turn cart validation rules on or off anytime with one click.

= 2. Stop at First Validation Error (Advanced Error Handling) =

Choose how validation errors behave:

✔ Show only the first error (clean UX)

✔ Show all matching validation errors at once

This gives store owners better control over customer experience.

= 3. Default Global Error Message =

Set a default validation error message that applies to all rules.

<strong>Example: </strong>"Your cart contains restricted items. Please review your cart before proceeding."

You can override this message per rule.

= 4. Advanced Conditional Rule Builder =

Create powerful rules using:

= ✔ Country-Based Restrictions =

Restrict purchases based on shipping country.

<strong>Example: </strong>"Alcohol cannot be purchased in restricted countries."

= ✔ Cart Contains Product =

Restrict specific product combinations.

<strong>Example: </strong>"A printer cannot be purchased with incompatible ink cartridges."

= ✔ Cart Contains Category =

Prevent mixing products from specific categories.

<strong>Example: </strong>"Customers cannot mix “Same-Day Delivery” items with regular products."

= ✔ User Role-Based Restrictions =

Apply rules based on customer roles.

<strong>Example: </strong>"Only logged-in users can purchase premium products."

= 5. AND / OR Conditional Logic =

Combine multiple conditions using:

- AND logic (all conditions must match)
- OR logic (any condition can match)

This allows advanced rule combinations like:

-- Restrict checkout if cart contains Category = “Alcohol” AND Country = “Germany”
Or
-- Restrict checkout if user role = Guest OR Country = Restricted List

= 5. Schedule Rules with Start & End Dates =

Set rule activation dates.

Perfect for:

- Holiday restrictions
- Temporary promotions
- Regulatory changes
- Seasonal product limitations

<strong>Example: </strong>"Restrict fireworks products outside festival dates."


== 🚀 Real World Use Cases ==

= 1. Restrict WooCommerce Checkout Based on Product and Shipping Country =

Example:

<ol>
<li>If cart contains “Alcohol”</li>
<li>AND shipping country = Restricted Country</li>
<li>Block checkout</li>
</ol>

Perfect for compliance-based businesses.

= 2. Same-Day Delivery Product Isolation =

<ol>
<li>If cart contains category “Same-Day Delivery”</li>
<li>Do not allow other categories.</li>
<li>Error message: Same-Day Delivery items must be purchased separately.</li>
</ol>

= 3. Members-Only Products =

<ol>
<li>If user role = Guest</li>
<li>AND cart contains Premium Category</li>
<li>Block checkout</li>
</ol>

= 4. Wholesale Protection =

<ol>
<li>If user role ≠ Wholesaler</li>
<li>AND cart contains Wholesale Category</li>
<li>Block checkout</li>
</ol>

= 4. Incompatible Product Protection =

<ol>
<li>If cart contains Product A</li>
<li>AND Product B</li>
<li>Show restriction error</li>
</ol>

Prevents order mistakes and support tickets.


== 💁 Who Is This Plugin For? ==

<ol>
<li>Alcohol & regulated product sellers</li>
<li>Wholesale & B2B stores</li>
<li>Membership stores</li>
<li>Delivery-based stores</li>
<li>International WooCommerce stores</li>
<li>Stores with incompatible product combinations</li>
<li>Compliance-heavy businesses</li>
</ol>


== ☎️ CONTACT US ==

<ul>
<li><strong>Free plugin:</strong> Need Technical Help? - <a href ="https://wordpress.org/support/plugin/cart-validation-for-woocommerce/" target="_blank">Click here</a></li>
</ul>

== Installation ==

= Minimum Requirements =

* WordPress 3.7 or greater
* PHP version 5.3.2 or greater
* MySQL version 5.0 or greater

= Automatic installation =

Automatic installation is the easiest option as WordPress handles the file transfers itself and you don't even need to leave your web browser. To do an automatic install of Cart Validation for WooCommerce, log in to your WordPress admin panel, navigate to the Plugins menu and click Add New.

In the search field type "Cart Validation for WooCommerce" and click Search Plugins. Once you have found our plugin you can install it by simply clicking Install Now. After clicking that link you will be asked if you are sure you want to install the plugin. Click yes and WordPress will automatically complete the installation.

= Manual installation =

The manual installation method involves downloading our plugin and uploading it to your web server via your favorite FTP application.

<ol>
<li>Download the plugin file to your computer and unzip it</li>
<li>Using an FTP program, or your hosting control panel, upload the unzipped plugin folder to your WordPress installation's wp-content/plugins/ directory.</li>
<li>Activate the plugin from the Plugins menu within the WordPress admin.</li>
</ol>

= Questions? =

If you have any questions please feel free to post them to the <a href="https://wordpress.org/support/plugin/cart-validation-for-woocommerce">**support forum**.</a>


== Frequently Asked Questions ==

= Does this plugin block checkout? =

Yes. If cart conditions match a restriction rule, checkout is blocked and an error message is displayed.

= Can I show multiple validation errors at once? =

Yes. You can enable or disable “Stop at First Validation Error” from general settings.

= Can I restrict checkout by country? =

Yes. You can block purchases based on the customer’s shipping country.

= Can I restrict product combinations? =

Yes. You can restrict specific products or categories from being purchased together.

= Can I schedule cart validation rules? =

Yes. You can set start and end dates for each rule.

= Does this work with user roles? =

Yes. You can restrict checkout based on WooCommerce user roles.

= Does this require coding knowledge? =

No. Everything works through an easy rule builder interface.


== Screenshots ==
1. 
2. 
3. 
4. 

== Upgrade Notice ==

Automatic updates should work great for you.  As always, though, we recommend backing up your site prior to making any updates just to be sure nothing goes wrong.

== Changelog ==

= 1.0.0 =
* Initial release