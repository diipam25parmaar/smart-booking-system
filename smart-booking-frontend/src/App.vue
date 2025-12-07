<template>
  <div class="app-container">
  <div class="data-layout">
    <h1>Smart Booking Scheduler</h1>

    <div class="layout">
      <!-- Client Booking Section -->
      <section class="card">
        <h2>Client Booking</h2>

        <div class="form-group">
          <label for="service">Service</label>
          <select id="service" v-model="booking.service_id" @change="onServiceOrDateChange">
            <option disabled value="">-- Select Service --</option>
            <option v-for="service in services" :key="service.id" :value="service.id">
              {{ service.name }} ({{ service.duration_minutes }} min)
            </option>
          </select>
        </div>

        <div class="form-group">
          <label for="date">Date</label>
          <input
            id="date"
            type="date"
            v-model="booking.date"
            @change="onServiceOrDateChange"
          />
        </div>

        <div class="form-group">
          <button @click="fetchSlots" :disabled="!booking.date || !booking.service_id">
            Load Available Slots
          </button>
        </div>

        <div class="slots">
          <h3>Available Slots</h3>
          <p v-if="slotsLoading">Loading slots...</p>
          <p v-else-if="slotsError" class="error">{{ slotsError }}</p>
          <p v-else-if="slots.length === 0">No slots available.</p>

          <div class="slots-grid">
            <button
              v-for="slot in slots"
              :key="slot.slot_key"
              class="slot-btn"
              :class="{ selected: selectedSlotKey === slot.slot_key }"
              :disabled="!slot.available"
              @click="selectSlot(slot)"
            >
              <span>{{ slot.label }}</span>
              <small v-if="!slot.available"> (booked)</small>
            </button>
          </div>
        </div>

        <div class="form-group">
          <label for="name">Your Name</label>
          <input id="name" type="text" v-model="booking.client_name" placeholder="John Doe" />
        </div>

        <div class="form-group">
          <label for="phone">Phone</label>
          <input id="phone" type="text" v-model="booking.client_phone" placeholder="+91 99999 99999" />
        </div>

        <div class="form-group">
          <label for="email">Your Email</label>
          <input id="email" type="email" v-model="booking.client_email" placeholder="you@example.com" />
        </div>

        <div class="form-group">
          <button @click="submitBooking" :disabled="!canSubmitBooking">
            Book Appointment
          </button>
        </div>

        <p v-if="bookingMessage" class="success">{{ bookingMessage }}</p>
        <p v-if="bookingError" class="error">{{ bookingError }}</p>
      </section>

      <!-- Admin Section -->
      <section class="card">
        <h2>Admin - Working Time Rules & Slot Overview</h2>

        <div class="info">
          <p>
            You can add weekly rules (e.g., Monday 09:00–17:00) OR specific date
            rules (e.g., 2025-12-25 closed).
          </p>
        </div>

        <div class="form-group">
          <label>Day of Week (1 = Monday, 7 = Sunday)</label>
          <input
            type="number"
            min="1"
            max="7"
            v-model.number="newRule.day_of_week"
            placeholder="Leave empty if using specific date"
          />
        </div>

        <div class="form-group">
          <label>Specific Date (optional)</label>
          <input
            type="date"
            v-model="newRule.date"
            placeholder="Overrides weekly rule for that day"
          />
        </div>

        <div class="form-group">
          <label>Start Time</label>
          <input type="time" v-model="newRule.start_time" />
        </div>

        <div class="form-group">
          <label>End Time</label>
          <input type="time" v-model="newRule.end_time" />
        </div>

        <div class="form-group">
          <label>
            <input type="checkbox" v-model="newRule.is_active" />
            Active
          </label>
        </div>

        <div class="form-group">
          <button @click="createRule">Add Rule</button>
        </div>

        <p v-if="ruleMessage" class="success">{{ ruleMessage }}</p>
        <p v-if="ruleError" class="error">{{ ruleError }}</p>

        <hr />

        <h3>Existing Rules</h3>
        <p v-if="rulesLoading">Loading rules...</p>
        <ul v-else class="rules-list">
          <li v-for="rule in rules" :key="rule.id">
            <strong>
              {{ rule.date ? rule.date : 'Day ' + rule.day_of_week }}
            </strong>
            :
            {{ rule.start_time }} - {{ rule.end_time }}
            ({{ rule.is_active ? "active" : "inactive" }})
          </li>
        </ul>

        <hr />

        <h3>Slots & Bookings for Selected Date</h3>
        <div class="tables">
          <div class="table-half">
            <h4>Booked Appointments</h4>
            <table class="simple-table" v-if="appointments.length">
              <thead>
              <tr>
                <th>#</th>
                <th>Service</th>
                <th>Name</th>
                <th>Phone</th>
                <th>Email</th>
                <th>Start</th>
                <th>End</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(a, idx) in appointments" :key="a.id">
                <td>{{ idx + 1 }}</td>
                <td>{{ a.service?.name ?? a.service_id }}</td>
                <td>{{ a.client_name ?? '—' }}</td>
                <td>{{ a.client_phone ?? '—' }}</td>
                <td>{{ a.client_email }}</td>
                <td>{{ formatLocal(a.start_at) }}</td>
                <td>{{ formatLocal(a.end_at) }}</td>
              </tr>
            </tbody>
            </table>
            <p v-else>No appointments for selected date.</p>
          </div>

          <div class="table-half">
            <h4>All Candidate Slots</h4>
            <table class="simple-table" v-if="slots.length">
              <thead>
                <tr>
                  <th>#</th>
                  <th>Slot</th>
                  <th>Available</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(s, idx) in slots" :key="s.slot_key">
                  <td>{{ idx + 1 }}</td>
                  <td>{{ s.label }}</td>
                  <td>{{ s.available ? 'Yes' : 'No' }}</td>
                </tr>
              </tbody>
            </table>
            <p v-else>No slots to show.</p>
          </div>
        </div>
      </section>
    </div>
    </div>
  </div>
</template>

<script setup>
import { onMounted, ref, computed } from "vue";
import api from "./api";

const services = ref([]);
const slots = ref([]);
const slotsLoading = ref(false);
const slotsError = ref("");

const booking = ref({
  service_id: "",
  date: "",
  start_time: "",
  client_email: "",
});

const bookingMessage = ref("");
const bookingError = ref("");
const selectedSlotKey = ref(null);

const rules = ref([]);
const rulesLoading = ref(false);
const ruleMessage = ref("");
const ruleError = ref("");

const newRule = ref({
  day_of_week: null,
  date: "",
  start_time: "",
  end_time: "",
  is_active: true,
});

const appointments = ref([]);

const canSubmitBooking = computed(() => {
  return (
    booking.value.service_id &&
    booking.value.date &&
    booking.value.start_time &&
    booking.value.client_email &&
    booking.value.client_name &&
    booking.value.client_phone
  );
});

function formatLocal(isoString) {
  if (!isoString) return "";
  const d = new Date(isoString);
  return `${d.toLocaleDateString()} ${String(d.getHours()).padStart(2,'0')}:${String(d.getMinutes()).padStart(2,'0')}`;
}

async function loadServices() {
  const { data } = await api.get("/services");
  services.value = data;
}

async function fetchSlots() {
  slotsError.value = "";
  bookingMessage.value = "";
  bookingError.value = "";
  slots.value = [];
  appointments.value = [];

  if (!booking.value.date || !booking.value.service_id) return;

  try {
    slotsLoading.value = true;
    const { data } = await api.get("/available-slots", {
      params: {
        date: booking.value.date,
        service_id: booking.value.service_id,
      },
    });

    // data.data is array of slots with available boolean
    slots.value = data.data;

    // Fetch appointments for this date for admin table
    const res = await api.get("/appointments", {
      params: { date: booking.value.date },
    });
    appointments.value = res.data.data || [];
  } catch (error) {
    slotsError.value =
      error.response?.data?.message ||
      "Failed to load slots. Please try again.";
  } finally {
    slotsLoading.value = false;
  }
}

function onServiceOrDateChange() {
  // reset selection
  selectedSlotKey.value = null;
  booking.value.start_time = "";
  fetchSlots();
}

function selectSlot(slot) {
  // save canonical slot key as selection
  selectedSlotKey.value = slot.slot_key;
  booking.value.start_time = slot.start_at.slice(11,16); // "HH:MM" from ISO
}

async function submitBooking() {
  bookingMessage.value = "";
  bookingError.value = "";

  if (!canSubmitBooking.value) {
    bookingError.value = "Please fill all fields.";
    return;
  }

  try {
    const payload = {
      date: booking.value.date,
      start_time: booking.value.start_time,
      service_id: booking.value.service_id,
      client_email: booking.value.client_email,
      client_name: booking.value.client_name,
      client_phone: booking.value.client_phone,
    };

    const { data } = await api.post("/appointments", payload);
    bookingMessage.value = data.message || "Appointment booked!";
    // Refresh slots and appointments after successful booking
    await fetchSlots();

    // clear selection and email
    selectedSlotKey.value = null;
    booking.value.client_email = "";
  } catch (error) {
    if (error.response?.data?.errors) {
      const errors = error.response.data.errors;
      bookingError.value = Object.values(errors).flat().join(" ");
    } else {
      bookingError.value =
        error.response?.data?.message ||
        "Failed to book appointment. Please try again.";
    }
    // Refresh slots anyway (in case of race)
    await fetchSlots();
  }
}

// admin: create rule
async function loadRules() {
  rulesLoading.value = true;
  ruleError.value = "";
  try {
    const { data } = await api.get("/working-time-rules");
    rules.value = data;
  } catch (error) {
    ruleError.value =
      error.response?.data?.message || "Failed to load rules.";
  } finally {
    rulesLoading.value = false;
  }
}

async function createRule() {
  ruleMessage.value = "";
  ruleError.value = "";

  if (!newRule.value.start_time || !newRule.value.end_time) {
    ruleError.value = "Start and end time are required.";
    return;
  }

  if (!newRule.value.day_of_week && !newRule.value.date) {
    ruleError.value =
      "Provide either a day_of_week or a specific date for the rule.";
    return;
  }

  try {
    const payload = {
      day_of_week: newRule.value.day_of_week || null,
      date: newRule.value.date || null,
      start_time: newRule.value.start_time,
      end_time: newRule.value.end_time,
      is_active: newRule.value.is_active,
    };

    await api.post("/working-time-rules", payload);
    ruleMessage.value = "Rule created successfully.";
    await loadRules();

    // reset rule
    newRule.value = {
      day_of_week: null,
      date: "",
      start_time: "",
      end_time: "",
      is_active: true,
    };

    // if admin created rule for currently selected date/service, refresh slots
    if (booking.value.date) {
      await fetchSlots();
    }
  } catch (error) {
    if (error.response?.data?.errors) {
      const errors = error.response.data.errors;
      ruleError.value = Object.values(errors).flat().join(" ");
    } else {
      ruleError.value =
        error.response?.data?.message || "Failed to create rule.";
    }
  }
}

onMounted(async () => {
  await loadServices();
  await loadRules();
});
</script>
<style scoped>
/* Reset page sizing so cards stack and occupy full content width */
html, body, #app {
  height: 100%;
  margin: 0;
  padding: 0;
}

/* App wrapper: keep gentle page padding but each card will fill the content area */
.app-container {
  min-height: 100vh;
  box-sizing: border-box;
  padding: 18px 28px;
  background: linear-gradient(180deg,#f8fbfd 0%, #f1f6fb 100%);
  font-family: Inter, system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial;
  color: #0f172a;
  display: flex;
  justify-content: center;
  align-items: flex-start;
}

/* Title */
h1 {
  margin: 8px 0 20px 0;
  font-size: 1.75rem;
  font-weight: 700;
  letter-spacing: -0.3px;
  color: #0b2447;
}

/* IMPORTANT: make layout single column (stacked). No side-by-side cards */
.layout {
  width: 100%;
  margin: 0;
  box-sizing: border-box;
}

/* Each card is a full-width stacked block */
.card {
  width: 100%;
  box-sizing: border-box;
  background: #ffffff;
  border: 1px solid rgba(15,23,42,0.12); /* clear border */
  border-radius: 10px;
  padding: 20px;
  margin-bottom: 18px; /* gap between stacked cards */
  box-shadow: 0 6px 18px rgba(15,23,42,0.04);
  transition: transform .12s ease, box-shadow .12s ease, border-color .12s ease;
}

.card:hover {
  transform: translateY(-2px);
  box-shadow: 0 12px 30px rgba(15,23,42,0.06);
}

/* Section headings inside cards */
.card > h2 {
  margin: 2px 0 8px;
  font-size: 1.15rem;
  color: #0b2447;
}

/* Compact descriptive text */
.info {
  font-size: 0.92rem;
  color: #475569;
  margin-bottom: 12px;
}

/* Form groups (vertical stacking inside each card) */
.form-group {
  margin-bottom: 12px;
  display: flex;
  flex-direction: column;
}

/* Labels */
label {
  font-size: 0.92rem;
  margin-bottom: 6px;
  color: #334155;
  font-weight: 600;
}

/* Inputs / selects */
input[type="text"],
input[type="email"],
input[type="date"],
input[type="time"],
input[type="number"],
select {
  padding: 10px 12px;
  font-size: 0.98rem;
  border-radius: 8px;
  border: 1px solid #dbe7f2;
  background: #fff;
  outline: none;
}

input:focus,
select:focus {
  border-color: #2563eb;
  box-shadow: 0 8px 18px rgba(37,99,235,0.06);
}

/* Primary button style */
button {
  display:inline-block;
  padding: 10px 14px;
  border-radius: 9px;
  border: 1px solid rgba(37,99,235,0.12);
  background: linear-gradient(180deg,#2563eb 0%, #1e4bd8 100%);
  color: white;
  font-weight: 700;
  cursor: pointer;
  transition: transform .12s ease, box-shadow .12s ease;
}

button:hover:not(:disabled) {
  transform: translateY(-3px);
  box-shadow: 0 10px 24px rgba(37,99,235,0.12);
}

button:disabled {
  background: linear-gradient(180deg,#9ca3af 0%, #94a3b8 100%);
  cursor: not-allowed;
  opacity: 0.92;
}

/* Slots grid: full-width horizontal wrapping within the card */
.slots-grid {
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
  margin-top: 8px;
}

/* Larger slot buttons (easier to click) */
.slot-btn {
  min-width: 120px;
  padding: 10px 12px;
  border-radius: 8px;
  border: 1px solid rgba(11,20,44,0.06);
  background: #fff;
  text-align: center;
  font-weight: 700;
  color: #08203a;
  cursor: pointer;
  transition: transform .08s ease, box-shadow .08s ease, border-color .08s ease;
}

.slot-btn:hover:not(:disabled) {
  transform: translateY(-3px);
  box-shadow: 0 8px 18px rgba(2,6,23,0.06);
  border-color: rgba(37,99,235,0.12);
}

.slot-btn.selected {
  background: linear-gradient(180deg,#2563eb 0%, #1e4bd8 100%);
  color: #fff;
  border-color: rgba(37,99,235,0.25);
}

.slot-btn[disabled] {
  opacity: 0.55;
  cursor: not-allowed;
  background: #f4f7fb;
  border: 1px dashed rgba(11,20,44,0.06);
  color: #6b7280;
}

/* Table containers stacked vertically and each full width */
.tables {
  display: block; /* stacked */
  width: 100%;
  margin-top: 12px;
}

/* Each table block (booked + slots) */
.table-half {
  width: 100%;
  overflow-x: auto;
  margin-bottom: 12px;
}

/* Strong bordered tables */
.simple-table {
  width: 100%;
  border-collapse: collapse;
  margin-top: 6px;
  box-sizing: border-box;
}

.simple-table thead th,
.simple-table tbody td {
  border: 2px solid rgba(15,23,42,0.08); /* stronger border */
  padding: 10px 12px;
  text-align: left;
  background: #fff;
  font-size: 0.95rem;
}

.simple-table thead th {
  background: #f3f7fb;
  font-weight: 800;
  color: #0b2447;
}

/* Row hover */
.simple-table tbody tr:hover td {
  background: linear-gradient(90deg, rgba(37,99,235,0.02), transparent);
}

/* Status small text */
.small {
  font-size: 0.86rem;
  color: #475569;
}

/* Messages */
.error { color:#b91c1c; margin-top:0.5rem; }
.success { color:#0f766e; margin-top:0.5rem; }

/* Responsive: keep single column on small screens */
@media (max-width: 920px) {
  .app-container { padding: 12px; }
  .card { padding: 14px; }
  .slot-btn { min-width: 96px; padding: 8px 10px; }
  .simple-table thead th, .simple-table tbody td { padding: 8px 10px; font-size: 0.92rem; }
}
body {
  display: flex; /* Make the body a flex container */
  justify-content: center; /* Center horizontally */
  align-items: center; /* Center vertically */
  min-height: 100vh; /* Ensure the body takes full viewport height */
  margin: 0; /* Remove default body margin */
}

</style>



