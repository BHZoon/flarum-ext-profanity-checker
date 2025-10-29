import app from 'flarum/admin/app';
import ExtensionPage from 'flarum/admin/components/ExtensionPage';

export default class ProfanityCheckerSettingsPage extends ExtensionPage {
  content() {
    return (
      <div className="container">
        <div className="Form">
          <div className="Form-group">
            <label>Gemini API Key</label>
            <input
              className="FormControl"
              bidi={this.setting('bhzoon.profanity_checker.api_key')}
              placeholder="Enter your Gemini API key"
            />
            <p className="helpText">
              Enter your Gemini API key to enable profanity checks.
            </p>
          </div>
          {this.submitButton()}
        </div>
      </div>
    );
  }
}
