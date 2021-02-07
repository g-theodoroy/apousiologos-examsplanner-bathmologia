@extends('layouts.app')

@section('content')

    <div class="container">
        <div class="columns is-marginless is-centered">
            <div class="column is-10">
                <nav class="card">
                    <header class="card-header">
                        <div class="level">
                            <p class="card-header-title level-item column is-narrow has-text-centered">
                                Πληροφορίες
                            </p>
                        </div>
                    </header>


                    <div class="card-content">
                        <p class="content">
                            Ο Ηλεκτρονικός Απουσιολόγος δημιουργήθηκε με σκοπό<br>
                            <b>α)</b> την καταγραφή των απουσιών των μαθητών <b>κάθε ώρα σε πραγματικό χρόνο</b><br>
                            <b>β)</b> την παροχή <b>άμεσης εποπτείας των απόντων μαθητών</b> από την 1η ώρα και κάθε ώρα<br>
                            <b>γ)</b> την <b>εισαγωγή των απουσιών στο myschool</b> άμα τη λήξη των μαθημάτων (εξαγωγή
                            αρχείου xls)
                        </p>
                        <p class="content">
                            Ακολούθησε ο εμπλουτισμός του με τα ακόλουθα:<br>
                            α) <b>Προγραμματιστής Διαγωνισμάτων</b>. Οι καθηγητές σε ένα εύχρηστο Ημερολόγιο
                            δηλώνουν τα διαγωνίσματά τους.<br>
                            β) <b>Καταχώριση Βαθμολογίας</b>. Καταχωρίζεται η βαθμολογία ανά τμήμα - μάθημα. Εξάγεται αρχείο 187.xls 
                            για εισαγωγή στο myschool.
                        </p>
                        <p class="content">
                            <a href="{{ asset('files/Οδηγίες ρύθμισης κ χρήσης Ηλ.Απουσιολόγου.pdf') }}"
                                target="_blank">Περισσοτερες πληροφορίες για τη ρύθμιση και χρήση.</a>.
                        </p>
                        <p class="content">
                            Χρησιμοποιεί τα δημοφιλή framework <a href="https://laravel.com/" target="_blank">Laravel</a>
                            και
                            <a href="https://bulma.io/" target="_blank">Bulma</a>.
                            Δημιουργήθηκε από τον <a
                                href="mailto:g.theodoroy@gmail.com?subject=Ηλεκτρονικός Απουσιολόγος">Γεώργιο Θεοδώρου</a>.
                        </p>
                        <p class="content">
                            Παρέχεται ως έχει σύμφωνα με την άδεια λογισμικού <a href="https://opensource.org/licenses/MIT"
                                target="_blank">MIT</a>.
                            Ο δημιουργός δεν φέρει ευθύνη για οτιδήποτε συμβεί κατά τη χρήση του.
                            </a>
                        </p>
                        <p class="content">
                            Διατίθεται δωρεάν. Αν σας διευκολύνει στην καθημερινότητά σας, σας τέρπει και επιθυμείτε,
                            μπορείτε να κάνετε μια δωρεά πατώντας το επόμενο κουμπί.
                            </a>
                        </p>
                        <div class="control has-text-centered">
                            <form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_top">
                                <input type="hidden" name="cmd" value="_s-xclick" />
                                <input type="hidden" name="hosted_button_id" value="LJ75HQPSHBK2Q" />
                                <input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_donateCC_LG.gif"
                                    border="0" name="submit" title="PayPal - The safer, easier way to pay online!"
                                    alt="Donate with PayPal button" />
                                <img alt="" border="0" src="https://www.paypal.com/en_GR/i/scr/pixel.gif" width="1"
                                    height="1" />
                            </form>
                        </div>

                    </div>

                </nav>
            </div>
        </div>
    </div>


@endsection
