// Encoding: utf-8

Calendar._DN = new Array
("Niedziela",
 "Poniedziałek",
 "Wtorek",
 "Środa",
 "Czwartek",
 "Piątek",
 "Sobota",
 "Niedziela");
Calendar._SDN = new Array
("Nie",
 "Pn",
 "Wt",
 "Śr",
 "Cz",
 "Pt",
 "So",
 "Nie");
Calendar._FD = 1;
Calendar._MN = new Array
("Styczeń",
 "Luty",
 "Marzec",
 "Kwiecień",
 "Maj",
 "Czerwiec",
 "Lipiec",
 "Sierpień",
 "Wrzesień",
 "Październik",
 "Listopad",
 "Grudzień");
Calendar._SMN = new Array
("Sty",
 "Lut",
 "Mar",
 "Kwi",
 "Maj",
 "Cze",
 "Lip",
 "Sie",
 "Wrz",
 "Paź",
 "Lis",
 "Gru");

// tooltips
Calendar._TT = {};
Calendar._TT["INFO"] = "O kalendarzu";

Calendar._TT["ABOUT"] =
"Wybór daty:\n" +
"- Użyj przycisków \xab, \xbb by wybrać rok\n" +
"- Użyj przycisków " + String.fromCharCode(0x2039) + ", " + String.fromCharCode(0x203a) + " by wybrać miesiąc\n" +
"- Przytrzymaj klawisz myszy nad jednym z powyższych przycisków dla szybszego wyboru.";
Calendar._TT["ABOUT_TIME"] = "\n\n" +
"Wybór czasu:\n" +
"- Kliknij na jednym z pól czasu by zwiększyć jego wartość\n" +
"- lub kliknij trzymając Shift by zmiejszyć jego wartość\n" +
"- lub kliknij i przeciągnij dla szybszego wyboru.";

Calendar._TT["PREV_YEAR"] = "Poprzedni rok (przytrzymaj)";
Calendar._TT["PREV_MONTH"] = "Poprzedni miesiąc (przytrzymaj)";
Calendar._TT["GO_TODAY"] = "Idź do dzisiaj";
Calendar._TT["NEXT_MONTH"] = "Następny miesiąc (przytrzymaj)";
Calendar._TT["NEXT_YEAR"] = "Następny rok (przytrzymaj)";
Calendar._TT["SEL_DATE"] = "Wybierz datę";
Calendar._TT["DRAG_TO_MOVE"] = "Przeciągnij by przesunąć";
Calendar._TT["PART_TODAY"] = " (dzisiaj)";

Calendar._TT["DAY_FIRST"] = "Wyświetl %s jako pierwszy";
Calendar._TT["WEEKEND"] = "0,6";

Calendar._TT["CLOSE"] = "Zamknij";
Calendar._TT["TODAY"] = "Dzisiaj";
Calendar._TT["TIME_PART"] = "Shift + kliknij, by zmienić";

// date formats
Calendar._TT["DEF_DATE_FORMAT"] = "%d-%m-%Y";
Calendar._TT["TT_DATE_FORMAT"] = "%A, %e %B";

Calendar._TT["WK"] = "ty";
Calendar._TT["TIME"] = "Godz:";
