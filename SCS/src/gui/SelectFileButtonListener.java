package gui;

import java.awt.event.ActionEvent;
import java.awt.event.ActionListener;

public class SelectFileButtonListener implements ActionListener {

    private MainFrame mainFrame;

    public SelectFileButtonListener(MainFrame mainFrame) {
        this.mainFrame = mainFrame;
    }

    @Override
    public void actionPerformed(ActionEvent e) {
        //TODO
        System.out.println("Select file button pressed!");
    }
}
