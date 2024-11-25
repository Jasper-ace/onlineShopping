import java.util.Scanner;

public class glenn {
    public static Scanner myScanner = new Scanner(System.in);

    public static void main(String[] args) {
        String[] strTermGrade = {"Prelim", "Midterm", "Finals", "Final Grading"};
        double dPrelim = getDouble("Enter " + strTermGrade[0] + ": ");
        double dMidterm = getDouble("Enter " + strTermGrade[1] + ": ");
        double dFinals = getDouble("Enter " + strTermGrade[2] + ": ");
        
        TermGrade grades = new TermGrade(dPrelim, dMidterm, dFinals);
        
        displayOutput(grades);
    }

    public static double getDouble(String strPrompt) {
        System.out.println(strPrompt);
        double dValue = myScanner.nextDouble();
        return dValue;
    }
    
    public static void displayOutput(TermGrade grades) {
        System.out.println("Prelim Grade: " + grades.dPrelim);
        System.out.println("Midterm Grade: " + grades.dMidterm);
        System.out.println("Finals Grade: " + grades.dFinals);
        
        double finalGrading = (grades.dPrelim + grades.dMidterm + grades.dFinals) / 3;
        System.out.println("Final Grading: " + finalGrading);
    }
}
