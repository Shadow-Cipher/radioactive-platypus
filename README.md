# radioactive-platypus
MT Connect Project, starting with Haas Milling Machines and Lathes, PHP, JavaScript, and HTML, and let's not forget, Linux.  We'll see. 

# Important Notes

## This code has not yet been tested in a production environment, so if you are going to attempt to use it in one, it is strongly suggested that you first setup a testing environment and make sure that everything is going to function as you would like for it to function. Hopefully this will change in the near future, but at present, it is still unclear as to whether or not I will have my testing environment made available to me for this project.  Use this code at your own risk, I guarantee nothing at all. 

That said, I do feel like this will all be functional relatively quickly. Feel free to make a pull request and contribute if you know what you are doing, I appreciate good help when it is offered.


---

| Feature                            | Implementation                           |
|------------------------------------|-----------------------------------------|
| Real-time multi-machine data collection | cURL Multi-handle (asynchronous)       |
| High-speed database storage       | MySQL batch inserts                     |
| WebSocket streaming               | Push updates to web clients             |
| Error handling & logs             | Robust logging with retries             |
| Dashboards & alerts               | Grafana, React.js, or Chart.js          |



# Install & Configure MTConnect Adapter

## Guide to Install and Configure MTConnect and Get the Data-Collection Server Up and Running

**By: CM Jones**  
For the **radioactive-platypus** MTConnect Project

---

## 1. Install & Configure MTConnect Adapter

The adapter reads machine data and formats it for the MTConnect Agent.

### Step 1: Install MTConnect Adapter

Clone the MTConnect Adapter repository:

```sh
git clone https://github.com/mtconnect/adapter
cd adapter
```

Install dependencies:

```sh
pip install pyserial
```

### Step 2: Configure `adapter.cfg`

Modify the adapter configuration to match your Haas machines:

```ini
Devices = haas_machines.xml
Adapter = 192.168.1.10:7878  # Replace with the IP of your first Haas machine
```

Save and exit.

### Step 3: Start the Adapter

```sh
python adapter.py
```

Repeat this for each machine.

---

## 2. Install & Configure MTConnect Agent

The agent collects data from multiple adapters and serves it via an HTTP API.

### Step 1: Install MTConnect Agent

Clone the agent repository:

```sh
git clone https://github.com/mtconnect/agent
cd agent
```

Install dependencies (if needed):

```sh
sudo apt install libxml2-utils
```

### Step 2: Configure `agent.cfg`

Edit the `agent.cfg` file:

```ini
Devices = haas_machines.xml
Adapters:
   milling_machine_1 = 192.168.1.10:7878
   lathe_1 = 192.168.1.11:7878
   milling_machine_2 = 192.168.1.12:7878
   lathe_2 = 192.168.1.13:7878
Port = 5000
```

Save and exit.

### Step 3: Start the Agent

```sh
./mtconnect_agent agent.cfg
```

---

## 3. Testing the Setup

Open a web browser and enter:

```sh
http://server-ip:5000/current
```

You should see XML-formatted machine data.

---

## License

This project is open-source and distributed under the GNU License.

## Contributing

Contributions are welcome! Please submit a pull request or open an issue.

## Contact

For any inquiries, please create an issue in this repository.
