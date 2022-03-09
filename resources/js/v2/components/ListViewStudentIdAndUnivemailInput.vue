<template>
  <div>
    <ListViewFormGroup :labelFor="studentIdInputName">
      <template #label>{{ studentIdLabel }}</template>
      <input
        :id="studentIdInputName"
        type="text"
        :class="[
          'form-control',
          $slots['invalid-student-id'] ? 'is-invalid' : ''
        ]"
        :name="studentIdInputName"
        required
        autocomplete="username"
        v-model="studentId"
      />
      <select
        :class="[
          'form-control',
          $slots['invalid-student-id'] ? 'is-invalid' : ''
        ]"
        :name="univemailDomainPartInputName"
        v-model="univemailDomainPart"
        v-if="appendSelectableDomainPartToStudentIdArea"
      >
        <option
          v-for="domainPart in allowedDomainParts"
          :value="domainPart"
          :key="domainPart"
        >
          @{{ domainPart }}
        </option>
      </select>
      <template v-slot:append v-if="appendFixedDomainPartToStudentIdArea">
        @{{ allowedDomainParts[0] }}
      </template>
      <input
        type="hidden"
        v-if="appendFixedDomainPartToStudentIdArea"
        :name="univemailDomainPartInputName"
        :value="allowedDomainParts[0]"
      />
      <template v-slot:invalid v-if="$slots['invalid-student-id']">
        <slot name="invalid-student-id" />
      </template>
    </ListViewFormGroup>
    <ListViewFormGroup v-if="allowArbitraryLocalPart">
      <template #label>{{ univemailLabel }}</template>
      <input
        :id="univemailLocalPartInputName"
        type="text"
        :class="[
          'form-control',
          $slots['invalid-univemail'] ? 'is-invalid' : ''
        ]"
        :name="univemailLocalPartInputName"
        v-model="univemailLocalPart"
        required
        autocomplete="username"
      />
      <select
        :class="[
          'form-control',
          $slots['invalid-univemail'] ? 'is-invalid' : ''
        ]"
        :name="univemailDomainPartInputName"
        v-model="univemailDomainPart"
        v-if="allowedDomainParts.length > 1"
      >
        <option
          v-for="domainPart in allowedDomainParts"
          :value="domainPart"
          :key="domainPart"
        >
          @{{ domainPart }}
        </option>
      </select>
      <template v-slot:append v-if="allowedDomainParts.length === 1">
        @{{ allowedDomainParts[0] }}
      </template>
      <input
        type="hidden"
        v-if="allowedDomainParts.length === 1"
        :name="univemailDomainPartInputName"
        :value="allowedDomainParts[0]"
      />
      <template v-slot:invalid v-if="$slots['invalid-univemail']">
        <slot name="invalid-univemail" />
      </template>
    </ListViewFormGroup>
    <input
      type="hidden"
      :name="univemailLocalPartInputName"
      :value="studentId"
      v-else
    />
  </div>
</template>

<script>
import ListViewFormGroup from './ListViewFormGroup.vue'

export default {
  components: {
    ListViewFormGroup
  },
  data() {
    return {
      studentId: '',
      univemailLocalPart: '',
      univemailDomainPart: ''
    }
  },
  props: {
    allowedDomainParts: {
      type: Array,
      required: true
    },
    allowArbitraryLocalPart: {
      type: Boolean,
      required: true
    },
    studentIdInputName: {
      type: String,
      required: true
    },
    univemailLocalPartInputName: {
      type: String,
      required: true
    },
    univemailDomainPartInputName: {
      type: String,
      required: true
    },
    studentIdLabel: {
      type: String,
      required: true
    },
    univemailLabel: {
      type: String,
      required: true
    },
    defaultStudentIdValue: {
      type: String,
      default: ''
    },
    defaultUnivemailLocalPartValue: {
      type: String,
      default: ''
    },
    defaultUnivemailDomainPartValue: {
      type: String,
      default: ''
    }
  },
  mounted() {
    this.studentId = this.defaultStudentIdValue
    this.univemailLocalPart = this.defaultUnivemailLocalPartValue
    this.univemailDomainPart = this.defaultUnivemailDomainPartValue
  },
  computed: {
    appendSelectableDomainPartToStudentIdArea() {
      return !this.allowArbitraryLocalPart && this.allowedDomainParts.length > 1
    },
    appendFixedDomainPartToStudentIdArea() {
      return (
        !this.allowArbitraryLocalPart && this.allowedDomainParts.length === 1
      )
    }
  }
}
</script>

<style></style>
