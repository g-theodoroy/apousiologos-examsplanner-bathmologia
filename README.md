# Ηλ. απουσιολόγος & Προγραμματιστής διαγωνισμάτων

## Σκοπός: 

- Η καταγραφή των απουσιών των μαθητών κάθε ώρα σε **πραγματικό χρόνο**.
- Άμεση **εποπτεία** των απόντων μαθητών από την 1η ώρα και κάθε ώρα.
- **Εισαγωγή των απουσιών στο myschool** άμα τη λήξη των μαθημάτων (εξαγωγή αρχείου xls).

- **Ο προγραμματισμός** των διαγωνισμάτων

- **Η καταχώριση** της βαθμολογίας

## Εγκατάσταση

#### linux terminal

```
git clone https://github.com/g-theodoroy/apousiologos-examsplanner.git
cd apousiologos-examsplanner/
composer install --no-dev
chmod -R 0777 storage/
```

#### windows

Κατεβάστε το zip:

https://github.com/g-theodoroy/apousiologos-examsplanner/archive/master.zip

Αποσυμπιέστε και με το **cmd** πηγαίνετε στον φάκελο και τρέξτε:
```
composer install --no-dev
```

Φυσικά θα πρέπει να έχετε εγκατεστημένη την **php** και τον **composer**

https://devanswers.co/install-composer-php-windows-10/

Αν θέλετε να ρυθμίσετε αποστολή **email** συμπληρώστε κατάλληλα στο αρχείο **.env**:

```
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=xxxxxxxxxx
MAIL_PASSWORD=xxxxxxxxxx
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=xxxxxxxxxx
MAIL_FROM_NAME="${APP_NAME}"
```

**Ανεβάστε τον φάκελο στον server**


## Ρύθμιση - χρήση

[Οδηγίες ρύθμισης κ χρήσης Ηλ.Απουσιολόγου.pdf](https://drive.google.com/file/d/17s1Oc0WNlOfuaPti7Tkr7cU3eop89RAP/view?usp=sharing)


# GΘ@2021



