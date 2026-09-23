import './bootstrap';
import '@fortawesome/fontawesome-free/css/all.min.css';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

const greyStoneLanguage = (() => {
    const storageKey = 'greyStoneLanguage';
    const defaultLanguage = 'en';
    const translations = new Map(Object.entries({
        'Free Shipping & More Offers': 'ফ্রি শিপিং ও আরও অফার',
        'Add to Cart': 'কার্টে যোগ দিন',
        'Add to cart': 'কার্টে যোগ দিন',
        'Add To Cart': 'কার্টে যোগ দিন',
        'ADD TO CART': 'কার্টে যোগ দিন',
        'Add Selected Items to Cart': 'নির্বাচিত আইটেম কার্টে যোগ দিন',
        'Adding Selected Items...': 'নির্বাচিত আইটেম যোগ হচ্ছে...',
        'Selected items added to cart.': 'নির্বাচিত আইটেম কার্টে যোগ হয়েছে।',
        'Selected items ready for checkout.': 'নির্বাচিত আইটেম চেকআউটের জন্য প্রস্তুত।',
        Selected: 'নির্বাচিত',
        'Buy Now': 'এখনই কিনুন',
        'Buy NOW': 'এখনই কিনুন',
        'BUY NOW': 'এখনই কিনুন',
        'In Stock': 'স্টকে আছে',
        'IN STOCK': 'স্টকে আছে',
        'Out Of Stock': 'স্টক শেষ',
        'Out of stock': 'স্টক শেষ',
        'OUT OF STOCK': 'স্টক শেষ',
        'Sold Out': 'স্টক শেষ',
        'SOLD OUT': 'স্টক শেষ',
        TRENDING: 'ট্রেন্ডিং',
        Trending: 'ট্রেন্ডিং',
        'Apply code': 'কোড প্রয়োগ করুন',
        'Applying...': 'প্রয়োগ হচ্ছে...',
        'Unable to apply coupon.': 'কুপন প্রয়োগ করা যায়নি।',
        'Coupon applied successfully.': 'কুপন সফলভাবে প্রয়োগ হয়েছে।',
        'Coupon saved. It will stay ready for your next checkout.': 'কুপন সেভ হয়েছে। পরের চেকআউটের জন্য প্রস্তুত থাকবে।',
        Applied: 'প্রয়োগ করা হয়েছে',
        Update: 'আপডেট',
        Apply: 'প্রয়োগ করুন',
        Account: 'অ্যাকাউন্ট',
        Welcome: 'স্বাগতম',
        Logout: 'লগ আউট',
        Login: 'লগ ইন',
        Register: 'রেজিস্টার',
        Home: 'হোম',
        Featured: 'ফিচারড',
        'New Arrivals': 'নতুন কালেকশন',
        Categories: 'ক্যাটাগরি',
        Products: 'প্রোডাক্ট',
        Audience: 'ধরন',
        Men: 'পুরুষ',
        Women: 'নারী',
        Category: 'ক্যাটাগরি',
        Brands: 'ব্র্যান্ড',
        Contact: 'যোগাযোগ',
        Search: 'সার্চ',
        Filters: 'ফিল্টার',
        All: 'সব',
        Sale: 'সেল',
        New: 'নতুন',
        'Price range': 'দামের সীমা',
        Tags: 'ট্যাগ',
        'Reset filters': 'ফিল্টার রিসেট',
        'Want more like this': 'এমন আরও দেখুন',
        'No products matched this search.': 'এই সার্চে কোনো প্রোডাক্ট পাওয়া যায়নি।',
        'COMING SOON': 'শীঘ্রই আসছে',
        'More Products are coming soon...': 'আরও প্রোডাক্ট শীঘ্রই আসছে...',
        'Active Coupons': 'অ্যাকটিভ কুপন',
        'Apply now or save one for your next checkout.': 'এখন প্রয়োগ করুন অথবা পরের চেকআউটের জন্য রেখে দিন।',
        DISCOUNT: 'ডিসকাউন্ট',
        COUPON: 'কুপন',
        'New customer': 'নতুন কাস্টমার',
        'Member exclusive': 'মেম্বার এক্সক্লুসিভ',
        'No active coupons are live right now.': 'এই মুহূর্তে কোনো অ্যাকটিভ কুপন নেই।',
        'Your Shopping Cart': 'আপনার শপিং কার্ট',
        Cart: 'কার্ট',
        Subtotal: 'সাবটোটাল',
        'Delivery charge will be calculated at checkout.': 'চেকআউটে ডেলিভারি চার্জ হিসাব করা হবে।',
        Cancel: 'বাতিল',
        'Proceed to Checkout': 'চেকআউটে যান',
        'Preparing Checkout...': 'চেকআউট প্রস্তুত হচ্ছে...',
        'Continue Shopping': 'শপিং চালিয়ে যান',
        'Saved Products': 'সেভ করা প্রোডাক্ট',
        'No saved products yet': 'এখনও কোনো প্রোডাক্ট সেভ করা নেই',
        'Tap a heart on any product to save it here.': 'কোনো প্রোডাক্ট সেভ করতে হার্ট আইকনে ট্যাপ করুন।',
        'Loading cart...': 'কার্ট লোড হচ্ছে...',
        Wishlist: 'উইশলিস্ট',
        Checkout: 'চেকআউট',
        'Delivery Information': 'ডেলিভারি তথ্য',
        'Enter the information required to deliver your order.': 'আপনার অর্ডার পৌঁছে দিতে প্রয়োজনীয় তথ্য দিন।',
        'Enter the information required to deliver': 'আপনার অর্ডার পৌঁছে দিতে',
        'your order.': 'প্রয়োজনীয় তথ্য দিন।',
        'Customer Information': 'কাস্টমার তথ্য',
        'We will contact you using this information.': 'এই তথ্য ব্যবহার করে আমরা আপনার সাথে যোগাযোগ করব।',
        'We will contact you using this': 'এই তথ্য ব্যবহার করে আমরা',
        information: 'আপনার সাথে যোগাযোগ করব।',
        Required: 'প্রয়োজনীয়',
        'Full Name': 'পূর্ণ নাম',
        'Enter your full name': 'আপনার পূর্ণ নাম লিখুন',
        'Phone Number': 'ফোন নম্বর',
        'Alternative Phone (Optional)': 'বিকল্প ফোন নম্বর (ঐচ্ছিক)',
        'Email Address (Optional)': 'ইমেইল ঠিকানা (ঐচ্ছিক)',
        'Delivery Location': 'ডেলিভারি লোকেশন',
        'Delivery charge depends on the selected area.': 'নির্বাচিত এলাকার ওপর ডেলিভারি চার্জ নির্ভর করে।',
        'Delivery charge depends on the': 'ডেলিভারি চার্জ নির্ভর করে',
        'selected area.': 'নির্বাচিত এলাকার ওপর।',
        'Inside Dhaka': 'ঢাকার ভিতরে',
        'Outside Dhaka': 'ঢাকার বাইরে',
        'Delivery charge ৳80': 'ডেলিভারি চার্জ ৳৮০',
        'Delivery charge ৳130': 'ডেলিভারি চার্জ ৳১৩০',
        District: 'জেলা',
        'Search district': 'জেলা সার্চ করুন',
        'Area / Thana': 'এরিয়া / থানা',
        'Enter area or thana': 'এরিয়া বা থানা লিখুন',
        'Order Note (Optional)': 'অর্ডার নোট (ঐচ্ছিক)',
        'Order Summary': 'অর্ডার সারাংশ',
        'Products Subtotal': 'প্রোডাক্ট সাবটোটাল',
        'Delivery Charge': 'ডেলিভারি চার্জ',
        'Coupon Discount': 'কুপন ডিসকাউন্ট',
        'Grand Total': 'সর্বমোট',
        'Cash on Delivery': 'ক্যাশ অন ডেলিভারি',
        Payment: 'পেমেন্ট',
        'Payment Method': 'পেমেন্ট পদ্ধতি',
        'Payment Summary': 'পেমেন্ট সারাংশ',
        'Payment will be collected when': 'অর্ডার ডেলিভারি হলে',
        'the order is delivered.': 'পেমেন্ট নেওয়া হবে।',
        'Pay after receiving your order': 'অর্ডার হাতে পেয়ে পেমেন্ট করুন',
        'Vouchers for your order': 'আপনার অর্ডারের ভাউচার',
        'Slide to choose the best active coupon.': 'সেরা অ্যাকটিভ কুপন বাছাই করতে স্লাইড করুন।',
        'Coupon Code': 'কুপন কোড',
        'Enter coupon code': 'কুপন কোড লিখুন',
        Remove: 'রিমুভ',
        'Review Order': 'অর্ডার রিভিউ করুন',
        'Confirm Your Order': 'অর্ডার নিশ্চিত করুন',
        'Order Items': 'অর্ডারের প্রোডাক্ট',
        'Yes, Confirm Order': 'হ্যাঁ, অর্ডার নিশ্চিত করুন',
        'Submitting...': 'সাবমিট হচ্ছে...',
        'Order Code': 'অর্ডার কোড',
        Pending: 'পেন্ডিং',
        'Sign In': 'সাইন ইন',
        'Sign Up': 'সাইন আপ',
        'Edit profile': 'প্রোফাইল এডিট',
        Or: 'অথবা',
        'Continue with Google': 'গুগল দিয়ে চালিয়ে যান',
        'CHANGE PHOTO': 'ছবি পরিবর্তন',
        'CHOOSE AN IMAGE': 'ছবি বাছাই করুন',
        Name: 'নাম',
        Email: 'ইমেইল',
        Gender: 'জেন্ডার',
        Male: 'পুরুষ',
        Female: 'নারী',
        'Mobile 1': 'মোবাইল ১',
        'Mobile 2': 'মোবাইল ২',
        optional: 'ঐচ্ছিক',
        Address: 'ঠিকানা',
        'Date of birth': 'জন্মতারিখ',
        Password: 'পাসওয়ার্ড',
        'Confirm password': 'পাসওয়ার্ড নিশ্চিত করুন',
        'Referral code': 'রেফারেল কোড',
        GO: 'এগিয়ে যান',
        'Already a member?': 'আগেই মেম্বার?',
        'Not a member?': 'এখনও মেম্বার নন?',
        'Join now': 'এখনই জয়েন করুন',
        'Sign in': 'সাইন ইন',
        Done: 'শেষ',
        'Last step': 'শেষ ধাপ',
        'Choose the division you call home.': 'আপনার নিজের বিভাগ বেছে নিন।',
        WELCOME: 'স্বাগতম',
        points: 'পয়েন্ট',
        'MEMBERSHIP OVERVIEW': 'মেম্বারশিপ ওভারভিউ',
        'ACTIVE MEMBER': 'অ্যাকটিভ মেম্বার',
        POINTS: 'পয়েন্ট',
        PURCHASES: 'পারচেজ',
        COUPONS: 'কুপন',
        PROFILE: 'প্রোফাইল',
        MILESTONES: 'মাইলস্টোন',
        'SHOP NOW': 'শপ করুন',
        'SIGN OUT': 'সাইন আউট',
        'MEMBER ACCOUNT': 'মেম্বার অ্যাকাউন্ট',
        'Profile Details': 'প্রোফাইল ডিটেইলস',
        'EDIT PROFILE': 'প্রোফাইল এডিট',
        MOBILE: 'মোবাইল',
        DIVISION: 'বিভাগ',
        'DATE OF BIRTH': 'জন্মতারিখ',
        'MEMBER REWARDS': 'মেম্বার রিওয়ার্ডস',
        'Coupon Wallet': 'কুপন ওয়ালেট',
        'No coupons yet': 'এখনও কোনো কুপন নেই',
        'Keep moving through your purchase milestones. Every coupon you unlock will appear here automatically.': 'পারচেজ মাইলস্টোন এগিয়ে নিন। আনলক করা সব কুপন এখানে স্বয়ংক্রিয়ভাবে দেখা যাবে।',
        'One coupon can be used per transaction.': 'প্রতি অর্ডারে একটি কুপন ব্যবহার করা যাবে।',
        'Sign out?': 'সাইন আউট করবেন?',
        'Are you sure you want to leave your member account?': 'আপনি কি নিশ্চিতভাবে মেম্বার অ্যাকাউন্ট থেকে বের হতে চান?',
        YES: 'হ্যাঁ',
        NO: 'না',
        Shirts: 'শার্ট',
        SHIRTS: 'শার্ট',
        Accessories: 'অ্যাকসেসরিজ',
        ACCESSORIES: 'অ্যাকসেসরিজ',
        Glasses: 'চশমা',
        GLASSES: 'চশমা',
        Shoes: 'জুতা',
        SHOES: 'জুতা',
        Shirt: 'শার্ট',
        SHIRT: 'শার্ট',
        Pants: 'প্যান্ট',
        PANTS: 'প্যান্ট',
        Cap: 'ক্যাপ',
        CAP: 'ক্যাপ',
        Polo: 'পোলো',
        POLO: 'পোলো',
        'Steel Knit Polo': 'স্টিল নিট পোলো',
        'Graphite Travel Cap': 'গ্রাফাইট ট্রাভেল ক্যাপ',
        'Navy Cargo Jogger': 'নেভি কার্গো জগার',
        'Aqua Shade Sunglasses': 'অ্যাকুয়া শেড সানগ্লাস',
        'Cobalt Street Sneakers': 'কোবাল্ট স্ট্রিট স্নিকার্স',
        'Skyline Casual Shirt': 'স্কাইলাইন ক্যাজুয়াল শার্ট',
        Baggy: 'ব্যাগি',
        Bags: 'ব্যাগ',
        BAGS: 'ব্যাগ',
        BedSheets: 'বেডশিট',
        BEDSheets: 'বেডশিট',
        BEDSHEETS: 'বেডশিট',
        'Blush Wide Pants': 'ব্লাশ ওয়াইড প্যান্ট',
        'Candy Mini Bag': 'ক্যান্ডি মিনি ব্যাগ',
        'Cotton Shirts': 'কটন শার্ট',
        'Cotton Shirts 2': 'কটন শার্ট ২',
        'Cotton Top': 'কটন টপ',
        'Indian Tops': 'ইন্ডিয়ান টপস',
        'Ocean Blue Denim': 'ওশান ব্লু ডেনিম',
        'Petal Soft Sneakers': 'পেটাল সফট স্নিকার্স',
        'Pink Aura Sunglasses': 'পিংক অরা সানগ্লাস',
        Purse: 'পার্স',
        PURSE: 'পার্স',
        'Rose Cloud Top': 'রোজ ক্লাউড টপ',
        Sneakers: 'স্নিকার্স',
        SNEAKERS: 'স্নিকার্স',
        Tops: 'টপস',
        TOPS: 'টপস',
        Tshirt: 'টি-শার্ট',
        TSHIRT: 'টি-শার্ট',
        'Stone Runner Sneakers': 'স্টোন রানার স্নিকার্স',
        'Charcoal Utility Pants': 'চারকোল ইউটিলিটি প্যান্ট',
        'Mercury Oxford Shirt': 'মার্কারি অক্সফোর্ড শার্ট',
        'Mercury Oxford Overshirt': 'মার্কারি অক্সফোর্ড ওভারশার্ট',
        'Mercury Chore Shirt': 'মার্কারি কোর শার্ট',
        'Mercury Chore Overshirt': 'মার্কারি কোর ওভারশার্ট',
        'Related Products': 'সম্পর্কিত প্রোডাক্ট',
        'Select Color': 'রং নির্বাচন করুন',
        'Choose your preferred color.': 'আপনার পছন্দের রং বাছাই করুন।',
        'Select Size & Quantity': 'সাইজ ও পরিমাণ নির্বাচন করুন',
        'Add quantity beside one or': 'এক বা একাধিক সাইজের পাশে',
        'more sizes.': 'পরিমাণ যোগ করুন।',
        'Trusted fashion and lifestyle shopping.': 'বিশ্বস্ত ফ্যাশন ও লাইফস্টাইল শপিং।',
        Explore: 'এক্সপ্লোর',
        EXPLORE: 'এক্সপ্লোর',
        Business: 'বিজনেস',
        BUSINESS: 'বিজনেস',
        Social: 'সোশ্যাল',
        SOCIAL: 'সোশ্যাল',
        'New Arrival': 'নতুন কালেকশন',
        'Featured Product': 'ফিচারড প্রোডাক্ট',
        'Factory Sweet Cool': 'সুইট কুল ফ্যাক্টরি',
        'Browse Categories': 'ক্যাটাগরি ব্রাউজ করুন',
        'All Products': 'সব প্রোডাক্ট',
        'Support Desk': 'সাপোর্ট ডেস্ক',
        'All rights reserved.': 'সর্বস্বত্ব সংরক্ষিত।',
        'Powered by Grey Stone retail and factory sourcing.': 'গ্রে স্টোন রিটেইল ও ফ্যাক্টরি সোর্সিং দ্বারা পরিচালিত।',
        'Smart fashion shopping': 'স্মার্ট ফ্যাশন শপিং',
        'Discover your style': 'আপনার স্টাইল খুঁজে নিন',
        'Browse by Category': 'ক্যাটাগরি অনুযায়ী ব্রাউজ করুন',
        'Page': 'পৃষ্ঠা',
        'of': 'এর',
        'Explore Categories →': 'ক্যাটাগরি দেখুন →',
        'View Products': 'প্রোডাক্ট দেখুন',
        Easy: 'সহজ',
        Fresh: 'নতুন',
        'Simple ordering': 'সহজ অর্ডারিং',
        'Latest collections': 'লেটেস্ট কালেকশন',
        'FEEL FREE TO VISIT OUR FACTORY IF YOU WANNA WORK WITH US...': 'আমাদের সাথে কাজ করতে চাইলে নিশ্চিন্তে ফ্যাক্টরি ভিজিট করুন...',
        'Feel free to visit our factory if you wanna work with us...': 'আমাদের সাথে কাজ করতে চাইলে নিশ্চিন্তে ফ্যাক্টরি ভিজিট করুন...',
        'Factory Gallery': 'ফ্যাক্টরি গ্যালারি',
        'Product Gallery': 'প্রোডাক্ট গ্যালারি',
        'More From Sweet Cool': 'সুইট কুল থেকে আরও',
        'Real production, workspace, and floor visuals from Sweet Cool.': 'সুইট কুলের বাস্তব প্রোডাকশন, ওয়ার্কস্পেস ও ফ্লোর ভিজ্যুয়াল।',
        'Products, samples, and sourcing-ready presentations in one stream.': 'প্রোডাক্ট, স্যাম্পল ও সোর্সিং-রেডি প্রেজেন্টেশন একসাথে।',
        'Campaign, mixed, and additional gallery visuals managed from your dashboard.': 'ড্যাশবোর্ড থেকে পরিচালিত ক্যাম্পেইন, মিক্সড ও অতিরিক্ত গ্যালারি ভিজ্যুয়াল।',
        'One Smart Form': 'একটি স্মার্ট ফর্ম',
        'Talk to Sweet Cool': 'সুইট কুলের সাথে কথা বলুন',
        'One compact form for factory connection, sourcing discussion, and a confirmed visit slot.': 'ফ্যাক্টরি কানেকশন, সোর্সিং আলোচনা ও ভিজিট স্লট কনফার্ম করার জন্য একটি সহজ ফর্ম।',
        Phone: 'ফোন',
        'Your full name': 'আপনার পূর্ণ নাম',
        'Phone or WhatsApp': 'ফোন বা হোয়াটসঅ্যাপ',
        'Email (Optional)': 'ইমেইল (ঐচ্ছিক)',
        'Company (Optional)': 'কোম্পানি (ঐচ্ছিক)',
        'Company or shop name': 'কোম্পানি বা দোকানের নাম',
        Requirement: 'প্রয়োজন',
        'Choose requirement': 'প্রয়োজন বাছাই করুন',
        'Bulk order': 'বাল্ক অর্ডার',
        'Factory sourcing': 'ফ্যাক্টরি সোর্সিং',
        'Custom production': 'কাস্টম প্রোডাকশন',
        'Wholesale partnership': 'হোলসেল পার্টনারশিপ',
        'Factory visit': 'ফ্যাক্টরি ভিজিট',
        'Buyer meeting': 'বায়ার মিটিং',
        'Preferred Contact (Optional)': 'পছন্দের যোগাযোগ মাধ্যম (ঐচ্ছিক)',
        'Choose contact method': 'যোগাযোগ মাধ্যম বাছাই করুন',
        'Factory Visit Booking (Optional)': 'ফ্যাক্টরি ভিজিট বুকিং (ঐচ্ছিক)',
        'Visit Date': 'ভিজিটের তারিখ',
        'Visit Time': 'ভিজিটের সময়',
        'Pick a date and time if you want us to reserve a Sweet Cool visit slot for you.': 'সুইট কুল ভিজিট স্লট রিজার্ভ করতে চাইলে তারিখ ও সময় বাছাই করুন।',
        'Who are you?': 'আপনি কে?',
        Entrepreneur: 'উদ্যোক্তা',
        Buyer: 'বায়ার',
        Others: 'অন্যান্য',
        Retailer: 'রিটেইলার',
        Wholesaler: 'হোলসেলার',
        'Sourcing Agent': 'সোর্সিং এজেন্ট',
        Message: 'মেসেজ',
        'Tell us which product, requirement, sourcing plan, or business goal you want to discuss.': 'কোন প্রোডাক্ট, প্রয়োজন, সোর্সিং প্ল্যান বা বিজনেস লক্ষ্য নিয়ে আলোচনা করতে চান তা লিখুন।',
        'Send to Sweet Cool': 'সুইট কুলে পাঠান',
        'Feel free to contact us': 'নিশ্চিন্তে আমাদের সাথে যোগাযোগ করুন',
        'Feel Free to Contact Us': 'যোগাযোগ করুন',
        'Your order has been received successfully. Our team will contact you soon for confirmation.': 'আপনার অর্ডার সফলভাবে গ্রহণ করা হয়েছে। কনফার্মেশনের জন্য আমাদের টিম শিগগিরই যোগাযোগ করবে।',
    }));

    const phraseTranslations = [
        ['Steel Knit Polo', 'স্টিল নিট পোলো'],
        ['Graphite Travel Cap', 'গ্রাফাইট ট্রাভেল ক্যাপ'],
        ['Navy Cargo Jogger', 'নেভি কার্গো জগার'],
        ['Aqua Shade Sunglasses', 'অ্যাকুয়া শেড সানগ্লাস'],
        ['Cobalt Street Sneakers', 'কোবাল্ট স্ট্রিট স্নিকার্স'],
        ['Skyline Casual Shirt', 'স্কাইলাইন ক্যাজুয়াল শার্ট'],
        ['Blush Wide Pants', 'ব্লাশ ওয়াইড প্যান্ট'],
        ['Candy Mini Bag', 'ক্যান্ডি মিনি ব্যাগ'],
        ['Cotton Shirts 2', 'কটন শার্ট ২'],
        ['Cotton Shirts', 'কটন শার্ট'],
        ['Cotton Top', 'কটন টপ'],
        ['Indian Tops', 'ইন্ডিয়ান টপস'],
        ['Ocean Blue Denim', 'ওশান ব্লু ডেনিম'],
        ['Petal Soft Sneakers', 'পেটাল সফট স্নিকার্স'],
        ['Pink Aura Sunglasses', 'পিংক অরা সানগ্লাস'],
        ['Rose Cloud Top', 'রোজ ক্লাউড টপ'],
        ['Stone Runner Sneakers', 'স্টোন রানার স্নিকার্স'],
        ['Charcoal Utility Pants', 'চারকোল ইউটিলিটি প্যান্ট'],
        ['Mercury Oxford Overshirt', 'মার্কারি অক্সফোর্ড ওভারশার্ট'],
        ['Mercury Oxford Shirt', 'মার্কারি অক্সফোর্ড শার্ট'],
        ['Mercury Chore Overshirt', 'মার্কারি কোর ওভারশার্ট'],
        ['Mercury Chore Shirt', 'মার্কারি কোর শার্ট'],
        ['Grey Stone', 'গ্রে স্টোন'],
        ['Blue Shades', 'ব্লু শেডস'],
        ['Pink Touch', 'পিংক টাচ'],
        ['Sweet Cool', 'সুইট কুল'],
        ['Cash on Delivery', 'ক্যাশ অন ডেলিভারি'],
        ['Proceed to Checkout', 'চেকআউটে যান'],
        ['Related Products', 'সম্পর্কিত প্রোডাক্ট'],
        ['New customer', 'নতুন কাস্টমার'],
        ['New Store Offer', 'নতুন স্টোর অফার'],
        ['Purchase', 'পারচেজ'],
        ['Reward', 'রিওয়ার্ড'],
        ['Journey begins', 'জার্নি শুরু'],
        ['Explore carefully selected fashion products, everyday essentials and new collections from', 'যত্নে বাছাই করা ফ্যাশন প্রোডাক্ট, দৈনন্দিন এসেনশিয়াল ও নতুন কালেকশন দেখুন'],
        ['VISIT OUR FACTORY IF YOU WANNA WORK WITH US', 'আমাদের সাথে কাজ করতে চাইলে ফ্যাক্টরি ভিজিট করুন'],
        ['with', 'সাথে'],
    ];

    const wordTranslations = new Map(Object.entries({
        active: 'অ্যাকটিভ',
        add: 'যোগ',
        address: 'ঠিকানা',
        all: 'সব',
        amount: 'পরিমাণ',
        aqua: 'অ্যাকুয়া',
        apply: 'প্রয়োগ',
        applied: 'প্রয়োগ হয়েছে',
        area: 'এরিয়া',
        arrival: 'কালেকশন',
        arrivals: 'কালেকশন',
        aura: 'অরা',
        available: 'অ্যাভেইলেবল',
        back: 'ফিরুন',
        bag: 'ব্যাগ',
        baggy: 'ব্যাগি',
        bags: 'ব্যাগ',
        bedsheets: 'বেডশিট',
        blue: 'ব্লু',
        blush: 'ব্লাশ',
        brand: 'ব্র্যান্ড',
        brands: 'ব্র্যান্ড',
        business: 'বিজনেস',
        browse: 'ব্রাউজ',
        buy: 'কিনুন',
        cap: 'ক্যাপ',
        cargo: 'কার্গো',
        cart: 'কার্ট',
        candy: 'ক্যান্ডি',
        casual: 'ক্যাজুয়াল',
        cash: 'ক্যাশ',
        categories: 'ক্যাটাগরি',
        category: 'ক্যাটাগরি',
        charcoal: 'চারকোল',
        checkout: 'চেকআউট',
        choose: 'বাছাই',
        close: 'বন্ধ',
        code: 'কোড',
        collect: 'কালেক্ট',
        collected: 'কালেক্টেড',
        color: 'রং',
        cobalt: 'কোবাল্ট',
        confirm: 'নিশ্চিত',
        contact: 'যোগাযোগ',
        continue: 'চালিয়ে যান',
        cool: 'কুল',
        cotton: 'কটন',
        coupon: 'কুপন',
        coupons: 'কুপন',
        customer: 'কাস্টমার',
        delivery: 'ডেলিভারি',
        denim: 'ডেনিম',
        details: 'ডিটেইলস',
        discount: 'ডিসকাউন্ট',
        discover: 'খুঁজে নিন',
        division: 'বিভাগ',
        email: 'ইমেইল',
        exclusive: 'এক্সক্লুসিভ',
        explore: 'এক্সপ্লোর',
        featured: 'ফিচারড',
        filters: 'ফিল্টার',
        free: 'ফ্রি',
        factory: 'ফ্যাক্টরি',
        full: 'পূর্ণ',
        gender: 'জেন্ডার',
        graphite: 'গ্রাফাইট',
        grey: 'গ্রে',
        glasses: 'চশমা',
        home: 'হোম',
        information: 'তথ্য',
        inside: 'ভিতরে',
        indian: 'ইন্ডিয়ান',
        item: 'আইটেম',
        items: 'আইটেম',
        jogger: 'জগার',
        knit: 'নিট',
        mercury: 'মার্কারি',
        loading: 'লোড হচ্ছে',
        location: 'লোকেশন',
        login: 'লগ ইন',
        logout: 'লগ আউট',
        member: 'মেম্বার',
        membership: 'মেম্বারশিপ',
        milestones: 'মাইলস্টোন',
        mobile: 'মোবাইল',
        name: 'নাম',
        navy: 'নেভি',
        new: 'নতুন',
        note: 'নোট',
        ocean: 'ওশান',
        off: 'ছাড়',
        offer: 'অফার',
        optional: 'ঐচ্ছিক',
        order: 'অর্ডার',
        overshirt: 'ওভারশার্ট',
        outside: 'বাইরে',
        oxford: 'অক্সফোর্ড',
        pants: 'প্যান্ট',
        password: 'পাসওয়ার্ড',
        payment: 'পেমেন্ট',
        phone: 'ফোন',
        petal: 'পেটাল',
        pink: 'পিংক',
        polo: 'পোলো',
        price: 'দাম',
        product: 'প্রোডাক্ট',
        products: 'প্রোডাক্ট',
        profile: 'প্রোফাইল',
        purse: 'পার্স',
        range: 'সীমা',
        referral: 'রেফারেল',
        register: 'রেজিস্টার',
        related: 'সম্পর্কিত',
        required: 'প্রয়োজনীয়',
        reset: 'রিসেট',
        reward: 'রিওয়ার্ড',
        rewards: 'রিওয়ার্ডস',
        rose: 'রোজ',
        runner: 'রানার',
        sale: 'সেল',
        save: 'সেভ',
        saved: 'সেভড',
        search: 'সার্চ',
        selected: 'নির্বাচিত',
        shades: 'শেডস',
        shade: 'শেড',
        shipping: 'শিপিং',
        shirt: 'শার্ট',
        shoes: 'জুতা',
        shop: 'শপ',
        shopping: 'শপিং',
        sign: 'সাইন',
        signin: 'সাইন ইন',
        signup: 'সাইন আপ',
        size: 'সাইজ',
        skyline: 'স্কাইলাইন',
        sneakers: 'স্নিকার্স',
        simple: 'সহজ',
        soft: 'সফট',
        social: 'সোশ্যাল',
        steel: 'স্টিল',
        stock: 'স্টক',
        street: 'স্ট্রিট',
        stone: 'স্টোন',
        style: 'স্টাইল',
        subtotal: 'সাবটোটাল',
        support: 'সাপোর্ট',
        tags: 'ট্যাগ',
        thana: 'থানা',
        top: 'টপ',
        tops: 'টপস',
        total: 'টোটাল',
        to: 'তে',
        touch: 'টাচ',
        travel: 'ট্রাভেল',
        trending: 'ট্রেন্ডিং',
        tshirt: 'টি-শার্ট',
        update: 'আপডেট',
        used: 'ব্যবহৃত',
        utility: 'ইউটিলিটি',
        valid: 'মেয়াদ',
        wallet: 'ওয়ালেট',
        welcome: 'স্বাগতম',
        wishlist: 'উইশলিস্ট',
    }));

    const skipSelector = [
        'script',
        'style',
        'textarea',
        'input',
        'code',
        '[data-no-translate]',
        '.notranslate',
    ].join(',');

    const textOriginals = new WeakMap();

    const normalise = (value) => value.replace(/\s+/g, ' ').trim();
    const hasBangla = (value) => /[\u0980-\u09ff]/.test(value);
    const toBanglaDigits = (value) => String(value).replace(/[0-9]/g, (digit) => '০১২৩৪৫৬৭৮৯'[Number(digit)]);
    const localiseBanglaText = (value) => toBanglaDigits(value);

    const translateText = (value) => {
        const clean = normalise(value);

        if (!clean) {
            return value;
        }

        if (/^add\s+to\s+cart$/i.test(clean)) {
            return 'কার্টে যোগ দিন';
        }

        if (/^add\s+selected\s+items\s+to\s+cart$/i.test(clean)) {
            return 'নির্বাচিত আইটেম কার্টে যোগ দিন';
        }

        if (/^adding\s+selected\s+items/i.test(clean)) {
            return 'নির্বাচিত আইটেম যোগ হচ্ছে...';
        }

        if (/^buy\s+now$/i.test(clean)) {
            return 'এখনই কিনুন';
        }

        if (/^(out\s+of\s+stock|sold\s+out)$/i.test(clean)) {
            return 'স্টক শেষ';
        }

        if (/^in\s+stock$/i.test(clean)) {
            return 'স্টকে আছে';
        }

        const pageCount = clean.match(/^Page\s+(\d+)\s+of\s+(\d+)$/i);
        if (pageCount) {
            return localiseBanglaText(`পৃষ্ঠা ${pageCount[1]} / ${pageCount[2]}`);
        }

        if (translations.has(clean)) {
            return localiseBanglaText(value.replace(clean, translations.get(clean)));
        }

        const percentOff = clean.match(/^(\d+(?:\.\d+)?)%\s*OFF$/i);
        if (percentOff) {
            return localiseBanglaText(`${percentOff[1]}% ছাড়`);
        }

        const takaOff = clean.match(/^৳([\d,]+)\s*OFF$/i);
        if (takaOff) {
            return localiseBanglaText(`৳${takaOff[1]} ছাড়`);
        }

        const saleOff = clean.match(/^(\d+(?:\.\d+)?)%\s*sale$/i);
        if (saleOff) {
            return localiseBanglaText(`${saleOff[1]}% সেল`);
        }

        let translatedValue = value;
        phraseTranslations.forEach(([english, bangla]) => {
            translatedValue = translatedValue.replace(
                new RegExp(english.replace(/[.*+?^${}()|[\]\\]/g, '\\$&'), 'gi'),
                bangla
            );
        });

        if (translatedValue !== value) {
            return localiseBanglaText(translatedValue);
        }

        const validUntil = clean.match(/^Valid until (.+)$/i);
        if (validUntil) {
            return localiseBanglaText(`মেয়াদ ${validUntil[1]} পর্যন্ত`);
        }

        const productCount = clean.match(/^(\d+) products$/i);
        if (productCount) {
            return localiseBanglaText(`${productCount[1]}টি প্রোডাক্ট`);
        }

        const itemCount = clean.match(/^(\d+) items$/i);
        if (itemCount) {
            return localiseBanglaText(`${itemCount[1]}টি আইটেম`);
        }

        const welcomeBrand = clean.match(/^Welcome to (.+)$/i);
        if (welcomeBrand) {
            return `${translateText(welcomeBrand[1])}-এ স্বাগতম`;
        }

        const minOrder = clean.match(/^Min\. order (.+)$/i);
        if (minOrder) {
            return localiseBanglaText(`নূন্যতম অর্ডার ${minOrder[1]}`);
        }

        const useBy = clean.match(/^Use by (.+)$/i);
        if (useBy) {
            return localiseBanglaText(`${useBy[1]} পর্যন্ত ব্যবহার করুন`);
        }

        const enterPhone = clean.match(/^Enter phone to verify$/i);
        if (enterPhone) {
            return 'ভেরিফাই করতে ফোন নম্বর দিন';
        }

        if (/^[A-Za-z][A-Za-z\s/&.-]*$/.test(clean) && clean.length <= 80) {
            const words = value.split(/(\s+|\/|&|-)/);
            const converted = words.map((word) => {
                const key = word.toLowerCase().replace(/[^a-z]/g, '');
                return wordTranslations.get(key) || word;
            }).join('');

            if (converted !== value) {
                return localiseBanglaText(converted);
            }
        }

        return localiseBanglaText(value);
    };

    const closeLanguageModal = () => {
        document.querySelectorAll('[data-language-modal]').forEach((modal) => {
            modal.classList.remove('open');
            modal.setAttribute('aria-hidden', 'true');
        });
    };

    const openLanguageModal = () => {
        const modal = document.querySelector('[data-language-modal]');
        if (!modal) {
            return;
        }

        modal.classList.add('open');
        modal.setAttribute('aria-hidden', 'false');
    };

    const setLanguage = (language, persist = true) => {
        const nextLanguage = language === 'bn' ? 'bn' : defaultLanguage;
        document.documentElement.lang = nextLanguage === 'bn' ? 'bn' : 'en';
        document.body.dataset.language = nextLanguage;
        document.body.classList.toggle('language-bn', nextLanguage === 'bn');
        if (persist) {
            localStorage.setItem(storageKey, nextLanguage);
        }
        document
            .querySelectorAll('[data-language-toggle]')
            .forEach((button) => {
                const isActive = button.dataset.languageToggle === nextLanguage;
                button.classList.toggle('active', isActive);
                button.setAttribute('aria-pressed', isActive ? 'true' : 'false');
            });
        applyTranslations();
    };

    const isTranslatablePage = () => (
        !document.querySelector('.admin-layout')
        && (
            document.body.classList.contains('member-body')
            || document.querySelector('.storefront')
            || document.querySelector('[data-language-toggle]')
        )
    );

    const collectTextNodes = () => {
        const walker = document.createTreeWalker(
            document.body,
            NodeFilter.SHOW_TEXT,
            {
                acceptNode(node) {
                    const parent = node.parentElement;

                    if (!parent || parent.closest(skipSelector)) {
                        return NodeFilter.FILTER_REJECT;
                    }

                    return normalise(node.nodeValue)
                        ? NodeFilter.FILTER_ACCEPT
                        : NodeFilter.FILTER_REJECT;
                },
            }
        );

        const textNodes = [];
        while (walker.nextNode()) {
            textNodes.push(walker.currentNode);
        }

        return textNodes;
    };

    const applyTranslations = () => {
        const language = localStorage.getItem(storageKey) || defaultLanguage;
        const textNodes = collectTextNodes();

        if (language !== 'bn') {
            textNodes.forEach((node) => {
                const original = textOriginals.get(node);
                if (original && node.nodeValue !== original) {
                    node.nodeValue = original;
                }
            });

            document.querySelectorAll('[data-i18n-placeholder]').forEach((node) => {
                if (node.getAttribute('placeholder') !== node.dataset.i18nPlaceholder) {
                    node.setAttribute('placeholder', node.dataset.i18nPlaceholder);
                }
            });

            return;
        }

        textNodes.forEach((node) => {
            if (!textOriginals.has(node)) {
                textOriginals.set(node, node.nodeValue);
            } else if (
                !hasBangla(node.nodeValue)
                && normalise(node.nodeValue) !== normalise(translateText(textOriginals.get(node)))
            ) {
                textOriginals.set(node, node.nodeValue);
            }
            const translated = translateText(textOriginals.get(node));
            if (node.nodeValue !== translated) {
                node.nodeValue = translated;
            }
        });

        document.querySelectorAll('input[placeholder], textarea[placeholder]').forEach((node) => {
            if (!node.dataset.i18nPlaceholder) {
                node.dataset.i18nPlaceholder = node.getAttribute('placeholder') || '';
            }
            const translated = translateText(node.dataset.i18nPlaceholder);
            if (node.getAttribute('placeholder') !== translated) {
                node.setAttribute('placeholder', translated);
            }
        });
    };

    document.addEventListener('click', (event) => {
        const button = event.target.closest('[data-language-toggle]');
        if (!button || !isTranslatablePage()) {
            return;
        }
        event.preventDefault();
        setLanguage(button.dataset.languageToggle);
        closeLanguageModal();
    });

    document.addEventListener('DOMContentLoaded', () => {
        if (!isTranslatablePage()) {
            document.documentElement.lang = 'en';
            document.body.classList.remove('language-bn');
            document.body.removeAttribute('data-language');
            return;
        }

        const language = localStorage.getItem(storageKey);
        setLanguage(language || defaultLanguage, Boolean(language));
        if (!language) {
            openLanguageModal();
        }

        const observer = new MutationObserver(() => {
            window.requestAnimationFrame(applyTranslations);
        });
        observer.observe(document.body, {
            childList: true,
            characterData: true,
            subtree: true,
        });
    });

    return { setLanguage };
})();

window.greyStoneLanguage = greyStoneLanguage;

document.addEventListener(
    'click',
    (event) => {
        const closeButton = event.target.closest(
            [
                '[data-close-modal]',
                '[data-close-admin-modal]',
                '[data-close-logout-modal]',
                '[data-close-admin-permission-modal]',
                '[data-close-root-passcode-modal]',
            ].join(',')
        );

        if (!closeButton) {
            return;
        }

        const modalId =
            closeButton.dataset.closeModal
            || closeButton.dataset.closeAdminModal;

        const modal =
            modalId
                ? document.getElementById(modalId)
                : closeButton.closest('.brand-modal');

        if (!modal || !modal.classList.contains('open')) {
            return;
        }

        event.preventDefault();
        event.stopImmediatePropagation();

        if (closeButton.matches('[data-close-root-passcode-modal]')) {
            window.dispatchEvent(
                new CustomEvent('admin-root-passcode-modal-closing')
            );
        }

        modal.classList.add('closing');
        modal.setAttribute('aria-hidden', 'true');

        window.setTimeout(() => {
            modal.classList.remove('open', 'closing');

            if (!document.querySelector('.brand-modal.open')) {
                document.body.classList.remove('brand-modal-open');
            }
        }, 210);
    },
    true
);

import './admin/brands.js';
import './admin/admin-users.js';
import './admin/categories.js';
import './admin/coupons.js';
import './admin/orders.js';
import './admin/products.js';
import './admin/reports.js';
import './admin/referrers.js';
import './admin/people-profiles.js';
import './admin/investments.js';
import './admin/search.js';
import './storefront/cart.js';
import './storefront/checkout.js';
import './storefront/category-filter';
import './storefront/coupon-popup';
import './storefront/launch';
