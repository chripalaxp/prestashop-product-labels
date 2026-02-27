## Product Labels – Module README

### Περιγραφή
Module για PrestaShop 1.6 / 1.7 που προσθέτει προσαρμοσμένα labels σε προϊόντα, π.χ. **Best Seller**, **New Arrival**, **Limited Offer**.  
Τα labels εμφανίζονται τόσο στη **λίστα προϊόντων** (category/search) όσο και στη **σελίδα προϊόντος**.

### Τι ζητήθηκε
- **Εμφάνιση label σε προϊόντα** με προκαθορισμένες τιμές:
  - None
  - Best Seller
  - New Arrival
  - Limited Offer
- **Εμφάνιση:**
  - Στη λίστα προϊόντων (category page, search results)
  - Στη σελίδα προϊόντος (product detail page)
- **Back office:**
  - Νέο πεδίο στη σελίδα επεξεργασίας προϊόντος (dropdown) για επιλογή label.
- **Front office:**
  - Εμφάνιση label μέσω hooks.
- **Τεχνικές απαιτήσεις:**
  - Να μη γίνουν αλλαγές σε core files του PrestaShop.
  - Label με **λευκά γράμματα** και **κόκκινο background**.
  - Το module να μπορεί να εγκαθίσταται / απεγκαθίσταται χωρίς σφάλματα.
  - Επιπλέον υλοποιήθηκε ρύθμιση **Enable/Disable** από admin.

### Τι υλοποιήθηκε
- **Βάση δεδομένων**
  - Πίνακας `ps_product_labels` με πεδία:
    - `id_product_labels`
    - `id_product`
    - `id_shop`
    - `label_type`
- **Ρυθμίσεις module**
  - Config key: `PRODUCT_LABELS_ENABLED` (switch στο admin → Enable Product Labels).
  - Όταν είναι κλειστό, δεν εμφανίζεται κανένα label και δεν φορτώνεται το front CSS του module.
- **Back office**
  - Extra panel στη φόρμα προϊόντος με dropdown:
    - None / Best Seller / New Arrival / Limited Offer.
  - Αποθήκευση/διαγραφή της επιλογής στον πίνακα `ps_product_labels`.
- **Front office**
  - Template `views/templates/front/product_label.tpl`:
    - Badge με κόκκινο background και λευκά γράμματα.
  - CSS στο `views/css/front.css` για το design του label.

### Hooks που χρησιμοποιούνται
- **Γενικά**
  - `header`: φόρτωση του front CSS όταν το module είναι enabled.
- **Back office (προϊόντα)**
  - `displayAdminProductsExtra`:
    - Προσθέτει το extra πεδίο (dropdown) στη σελίδα επεξεργασίας προϊόντος.
  - `actionProductSave`:
    - Αποθηκεύει ή διαγράφει το επιλεγμένο label στη βάση ανά προϊόν/κατάστημα.
- **Front office (λίστα προϊόντων)**
  - `displayProductListFunctionalButtons` (κυρίως 1.7 themes):
    - Χρησιμοποιείται για την εμφάνιση του label στη λίστα προϊόντων (δίπλα στα functional buttons).
  - `displayProductListReviews` (συχνό σε 1.6 themes):
    - Εναλλακτικό hook για εμφάνιση label σε product listing, κάτω από όνομα/reviews.
- **Front office (σελίδα προϊόντος)**
  - `displayProductActions`:
    - Εμφάνιση label στο block ενεργειών του προϊόντος (κοντά σε Add to cart) σε 1.7 themes.

Όλοι οι παραπάνω hooks χρησιμοποιούνται χωρίς καμία αλλαγή σε core αρχεία του PrestaShop, διασφαλίζοντας συμβατότητα με PrestaShop **1.6.x** και **1.7.x**.
