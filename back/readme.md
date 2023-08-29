# 📚 Database architecture 📚

The database architecture is pretty straighforward and will not be fully detailed here.
However there are some points which might require explaining:

- **lifecycle_iteration**: A *LI* simply represents a point in time where a company update occured. The time interval between two lifecycle iteration doesn't have to be constant as each *LI* holds the Date at which it was created
- **price_history**: Every time a company's price is updated, it's price is also recorded in a *price_history* which itself points to a *LI*.
  Note that in the way the updating works, A *LI* is always created before prices are updated, which means *price_history* holds current data too.

# 📞 API 📞

The frontend interacts with the server through API calls. Each API route starts with `server_adress/api/call`

### SecurityController

allows the user to register, login and re-receive it's user data. The data sent back to the user should always be non-sensitive data

### RetrieveCompaniesController

contains various routes for retrieving companies

- **/api/retrieve_companies**: It can take two numerical parameters `amount` and `offset`.
  - `amount` is the number of returned companies
  - `offset` is from where should we start selecting companies
- **/api/retrieve_updated**: Returns the companies that were last updated when `si:update-companies` was called and the next update date
- **/api/company_details/{id}**: Returns the details of a specific companies along with it's full price history

### OrderController

the route for emitting a buy or sell order

- **/api/emit_order**: emits an order. Returns the player's balance after the operation.
  - `company_id` The id of the company on which order is placed
  - `amount` How much is sold/bought
  - `type` 'buy' or 'sell'

# ⚙️ Commands ⚙️

## Creating companies

Companies can be created using the command: `bin/console si:create-companies [--test]`

When calling this command, new companies are inserted into the database with random price and trend if they don't exist. If they do exist, their prices and trends are regenerated randomly.

Be aware that this command create history elements on the current `LifecycleIteration` or creates the first one if none exist.

In the `assets/config` folder are two files:

- `company-def.json`
- `company-def.test.json`

The first file contains the information about all companies that are to be created when the command is called.
If the `--test` flag is used, the second file is used instead.

## Updating companies

Companies are updated by calling the command: `bin/console si:update-companies`. This command is expected to be called periodically.

It always update trends for all companies and randomly update companies' prices based on their trend. Companies with higher trends have a larger probability to be updated.

You can modify the update behaviour in the class under `src/Service/UpdateCompanies.php`

When this command has finished running, it caches two things:

- The internal `nextUpdate` date based on `UPDATE_INTERVAL` environmental variable. This value should be in the PHP format described [here](https://www.php.net/manual/en/dateinterval.construct.php).
- A list of updated companies' ids

The `nextUpdate` date is used only for informative purpose when an API call is made. The command will not rerun at this date automatically, you are expected to set this up yourself using *crons*
