package gui;

import java.awt.event.ActionEvent;
import java.awt.event.ActionListener;

public class SubmitButtonListener implements ActionListener {

    private MainFrame mainFrame;

    public SubmitButtonListener(MainFrame mainFrame) {
        this.mainFrame = mainFrame;
    }

    @Override
    public void actionPerformed(ActionEvent e) {
        //TODO
        System.out.println("Submit button pressed!");
    }
}
