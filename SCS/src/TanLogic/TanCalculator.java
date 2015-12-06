package TanLogic;

import java.io.File;
import java.io.FileNotFoundException;
import java.io.FileWriter;
import java.io.IOException;
import java.io.UnsupportedEncodingException;
import java.io.Writer;
import java.security.MessageDigest;
import java.security.NoSuchAlgorithmException;
import java.text.DateFormat;
import java.text.SimpleDateFormat;
import java.util.Date;
import java.util.Scanner;

public class TanCalculator {

    public TanCalculator() {}

    /**
     * Function to use for single transactions.
     * @param pin The users pin number.
     * @param transferAmount Amount to transfer to target account.
     * @param targetAccount Target account number.
     * @return Returns the generated tan as a String.
     */
    public String getTan(String pin, double transferAmount, String targetAccount) {
        double transferAmountArray[] = new double[1];
        String targetAccountArray[] = new String[1];
        transferAmountArray[0] = transferAmount;
        targetAccountArray[0] = targetAccount;
        return getTan(pin, transferAmountArray, targetAccountArray);
    }

    /**
     * Function to use for transaction batch files.
     * @param pin The users pin number.
     * @param transferAmount Amount to transfer to target account for corresponding transaction (Array).
     * @param targetAccount Array of target account numbers for every transaction.
     * @return Returns the generated tan as a String.
     */
    public String getTan(String pin, double[] transferAmount, String[] targetAccount) {
    	File f = new File("counter");
    	int counter = 0;
		try (Scanner scanner = new Scanner(f)) {
	    	if(scanner.hasNextInt())
	    	{
	    	     counter = scanner.nextInt();
	    	}
		} catch (FileNotFoundException e) {
			System.out.println("No counter file found. Starting at 0.");
		}
		
		counter++;
		byte[] pinDigest;
		String pinDigestString = "";
		String finalString = "";
		MessageDigest md;
		try {
			md = MessageDigest.getInstance("SHA-256");
			//Hash pin counter times
			pinDigest = pin.getBytes("UTF-8");
			for (int i = 0; i < counter; i++)
			{
				md.update(pinDigest);
				pinDigest = md.digest();
				System.out.println("Hashed PIN in loop: " + this.bytesToHexString(pinDigest));
				pinDigestString = bytesToHexString(pinDigest);
				pinDigest = pinDigestString.getBytes("UTF-8");
				
			}
			
			//Concatenate pin hash with other data and hash it
			StringBuilder builder = new StringBuilder(pinDigestString);
			for(int i = 0; i < transferAmount.length; i++)
			{
				builder.append(targetAccount[i]);
				//builder.append(transferAmount[i]);
			}
			DateFormat dateFormat = new SimpleDateFormat("yyyyMMdd");
			Date date = new Date();
			builder.append(dateFormat.format(date));
			md.update(builder.toString().getBytes("UTF-8"));
			finalString = bytesToHexString(md.digest());
		} catch (NoSuchAlgorithmException | UnsupportedEncodingException e) {
			e.printStackTrace();
		}
		
		finalString = Integer.toString(counter) + "+" + finalString;
		Writer wr;
		try {
			wr = new FileWriter("counter");
			wr.write(Integer.toString(counter));
			wr.close();
		} catch (IOException e) {
			e.printStackTrace();
		}
    	
        return finalString;
    }
    
    private String bytesToHexString(byte[] bytes) {
        StringBuffer hexBuffer = new StringBuffer();
        for (int i = 0; i < bytes.length; i++) {
            hexBuffer.append(Integer.toString((bytes[i] & 0xFF) + 0x100, 16).substring(1));
        }

        return hexBuffer.toString();
    }
}
