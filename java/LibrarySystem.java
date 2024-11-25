abstract class LibraryItem {
    protected String title;
    protected String author;
    protected int publicationYear;

    public LibraryItem(String title, String author, int publicationYear) {
        this.title = title;
        this.author = author;
        this.publicationYear = publicationYear;
    }

    public abstract void displayInfo();
}

class Book extends LibraryItem {
    private int totalPages; 

    public Book(String title, String author, int publicationYear, int totalPages) {
        super(title, author, publicationYear);
        this.totalPages = totalPages;
    }

    @Override
    public void displayInfo() {
        System.out.println("Book: " + title + ", Author: " + author + ", Year: " + publicationYear + ", Pages: " + totalPages);
    }
}

class Magazine extends LibraryItem {
    private int issueNumber; 

    public Magazine(String title, String author, int publicationYear, int issueNumber) {
        super(title, author, publicationYear);
        this.issueNumber = issueNumber;
    }

    @Override
    public void displayInfo() {
        System.out.println("Magazine: " + title + ", Author: " + author + ", Year: " + publicationYear + ", Issue No: " + issueNumber);
    }
}

class DVD extends LibraryItem {
    private int duration; 

    public DVD(String title, String author, int publicationYear, int duration) {
        super(title, author, publicationYear);
        this.duration = duration;
    }

    @Override
    public void displayInfo() {
        System.out.println("DVD: " + title + ", Director: " + author + ", Year: " + publicationYear + ", Duration: " + duration + " mins");
    }
}

public class LibrarySystem {
    public static void main(String[] args) {
        Book book = new Book("The Great Gatsby", "F. Scott Fitzgerald", 1925, 218);
        Magazine magazine = new Magazine("National Geographic", "Various Authors", 2023, 101);
        DVD dvd = new DVD("Inception", "Christopher Nolan", 2010, 148);

        book.displayInfo();
        magazine.displayInfo();
        dvd.displayInfo();
    }
}
