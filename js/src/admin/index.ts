import app from 'flarum/admin/app';
import ProfanityCheckerSettingsModal from './components/ProfanityCheckerSettingsModal';

app.initializers.add('bhzoon-profanity-checker', () => {
  app.extensionData
    .for('bhzoon-profanity-checker')
    .registerPage(ProfanityCheckerSettingsModal);
});
