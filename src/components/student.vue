<template>
  <div class="p-5">
    <div class="w-full flex">
      <div class="w-full">
        <form @submit.prevent="addStudent" class="">
          <input
            type="text"
            v-model="name"
            placeholder="Name"
            class="w-1/2 border-2 border-black border-opacity-20"
            required
          />
          <div class="w-40 h-40 border-2 border-black my-2">
            <img
              v-if="showimg"
              :src="showimg"
              alt="Image Preview"
              class="w-fit h-40"
            />
            <img v-if="imgUrl" :src="imgUrl" alt="" class="w-fit h-40" />
          </div>
          <input type="file" @change="onFileChange" ref="fileInput" /><br />
          <button type="submit" class="btn mt-2">
            {{ edit ? "Edit" : "Add" }} Student
          </button>
          <button
            type="button"
            @click="clearForm"
            class="btn bg-red-500 text-white ml-2"
          >
            Clear
          </button>
          <button
            v-if="showimg || imgUrl"
            type="button"
            @click="reamoveImage"
            class="btn bg-green-500 text-white ml-2"
          >
            Remove Image
          </button>
        </form>
        <!-- <p v-if="errorMessage" class="text-red-400">{{ errorMessage }}</p> -->
      </div>
      <div class="w-full">
        <table>
          <thead>
            <tr>
              <th>#ID</th>
              <th>Name</th>
              <th>Image</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="student in students">
              <td>{{ student.stu_id }}</td>
              <td>{{ student.stu_name }}</td>
              <td>
                <img
                  :src="
                    'http://localhost/path/to/your/project/src/assets/uploads/' +
                    student.stu_img
                  "
                  alt="Student Image"
                  width="50"
                  v-if="student.stu_img"
                />
              </td>

              <td class="flex">
                <button
                  @click="
                    handleClick(
                      student.stu_id,
                      student.stu_name,
                      student.stu_img
                    )
                  "
                  type="button"
                  class="ml-5 bg-green-500 px-4 p-2 text-white"
                >
                  EDIT
                </button>
                <button
                  @click="deleteStudent(student.stu_id)"
                  class="bg-red-500 px-4 p-2 text-white"
                >
                  Delete
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>
<script>
import axios from "axios";

export default {
  data() {
    return {
      students: [],
      id: null,
      name: "",
      img: null,
      imgUrl: null,
      showimg: null,
      errorMessage: "",
      edit: "",
      file: null,
    };
  },
  created() {
    this.fetchStudents();
  },
  methods: {
    fetchStudents() {
      axios
        .get("http://localhost/path/to/your/php/student.php?action=select")
        .then((response) => {
          this.students = response.data;
        })
        .catch((error) => {
          console.error(error);
        });
    },
    addStudent() {
      if (this.edit) {
        this.updateStudent();
      } else {
        const formData = new FormData();
        formData.append("stu_name", this.name);
        formData.append("stu_img", this.img);
        axios
          .post(
            "http://localhost/path/to/your/php/student.php?action=insert",
            formData,
            {
              headers: {
                "Content-Type": "multipart/form-data",
              },
            }
          )
          .then((response) => {
            if (response.data.message === "Already Exist") {
              this.errorMessage = "Student with this name already exists.";
              this.clearForm();
            } else {
              console.log(response.data);
              this.fetchStudents(); // Refresh the student list
              this.clearForm();
              this.errorMessage = "";
            }
          })
          .catch((error) => {
            console.error(error);
            this.errorMessage = "An error occurred while adding the student.";
          });
      }
    },
    deleteStudent(id) {
      axios
        .post(
          "http://localhost/path/to/your/php/student.php?action=delete",
          { stu_id: id },
          {
            headers: {
              "Content-Type": "application/json",
            },
          }
        )
        .then((response) => {
          console.log(response.data);
          this.fetchStudents(); // Refresh the student list
          this.clearForm();
        })
        .catch((error) => {
          console.error(error);
        });
    },
    updateStudent() {
      const formData = new FormData();
      formData.append("stu_id", this.id);
      formData.append("stu_name", this.name);
      if (this.img != null) {
        formData.append("stu_img", this.img);
      } else {
        formData.append("stu_img", "");
      }
      axios
        .post(
          "http://localhost/path/to/your/php/student.php?action=update",
          formData,
          {
            headers: {
              "Content-Type": "multipart/form-data",
            },
          }
        )
        .then((response) => {
          console.log(response.data);
          this.fetchStudents(); // Refresh the student list
          this.clearForm();
        })
        .catch((error) => {
          console.error(error);
        });
    },
    handleClick(id, name, img) {
      this.clearForm();
      this.id = id;
      this.name = name;
      if (img) {
        this.img = img;
        this.imgUrl =
          "http://localhost/path/to/your/project/src/assets/uploads/" + img;
      }
      this.edit = "edit";
    },
    onFileChange(event) {
      this.imgUrl = null;
      const file = event.target.files[0];
      if (file) {
        this.img = file;
        const reader = new FileReader();
        reader.onload = (e) => {
          this.showimg = e.target.result;
        };
        reader.readAsDataURL(file);
      } else {
        this.showimg = null;
      }
    },
    reamoveImage() {
      this.img = null;
      this.imgUrl = null;
      this.showimg = null;
    },
    clearForm() {
      this.id = null;
      this.name = "";
      this.img = null;
      this.imgUrl = null;
      this.showimg = null;
      this.edit = null;
      const input = this.$refs.fileInput; // Assuming you have a ref named 'fileInput' on your input element
      if (input) {
        input.value = ""; // Reset the value of the input
      }
    },
  },
};
</script>
